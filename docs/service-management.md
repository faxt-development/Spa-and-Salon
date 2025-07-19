# Service Management Technical Documentation

## Overview

The Service Management system in Faxtina allows administrators to create, edit, delete, and manage services for companies. Services can be assigned to companies, and companies can have multiple services. This document provides technical details on how the service management system works, including the soft delete functionality and template services.

## Database Schema

### Services Table

The services table stores all available services in the system.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Name of the service |
| description | text | Description of the service |
| price | decimal | Price of the service |
| duration | integer | Duration of the service in minutes |
| template | boolean | Whether this is a template service (read-only) |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### Company_Service Pivot Table

This pivot table manages the many-to-many relationship between companies and services.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| company_id | bigint | Foreign key to companies table |
| service_id | bigint | Foreign key to services table |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

## Soft Delete Implementation

Both the `services` table and the `company_service` pivot table implement soft deletes. This means that when a service or a company-service relationship is deleted, it's not actually removed from the database. Instead, the `deleted_at` column is set to the current timestamp.

### Migration

The soft delete functionality was added via a migration:

```php
public function up()
{
    Schema::table('services', function (Blueprint $table) {
        $table->softDeletes();
        $table->boolean('template')->default(false)->after('duration');
    });

    Schema::table('company_service', function (Blueprint $table) {
        $table->softDeletes();
    });
}
```

### Model Configuration

Both the `Service` model and the pivot relationship in the `Company` model are configured to use soft deletes:

```php
// Service.php
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    
    // ...
}

// Company.php
public function services()
{
    return $this->belongsToMany(Service::class)
        ->withTimestamps()
        ->withPivot('id')
        ->withTrashed();
}
```

## Template Services

Template services are special services that are read-only and cannot be deleted. They are identified by the `template` boolean field in the `services` table.

### Backend Protection

The `ServiceController` includes logic to prevent deletion or modification of template services:

```php
public function destroy(Service $service)
{
    if ($service->template) {
        return redirect()->back()
            ->with('error', 'Template services cannot be deleted.');
    }
    
    // Delete logic...
}

public function removeFromCompany(Request $request, Company $company, Service $service)
{
    if ($service->template) {
        return response()->json([
            'success' => false,
            'message' => 'Template services cannot be removed.'
        ], 403);
    }
    
    // Remove logic...
}

public function update(Request $request, Service $service)
{
    if ($service->template) {
        return redirect()->back()
            ->with('error', 'Template services cannot be modified.');
    }
    
    // Update logic...
}
```

### Copying Template Services for Editing

Since template services cannot be directly edited, the system provides a mechanism to create editable copies of template services. When a user wants to modify a template service, the following process occurs:

1. A new service record is created as a copy of the template service
2. The company's relationship with the template service is removed
3. A new relationship is created between the company and the copied service
4. The user can then edit the copied service as needed

```php
public function copyTemplateService(Request $request, Company $company, Service $service)
{
    // Verify this is a template service
    if (!$service->template) {
        return response()->json([
            'success' => false,
            'message' => 'Only template services can be copied.'
        ], 400);
    }
    
    DB::beginTransaction();
    
    try {
        // Create a copy of the service
        $newService = $service->replicate();
        $newService->template = false;
        $newService->name = $newService->name . ' (Custom)';
        $newService->save();
        
        // Copy categories
        $newService->categories()->attach($service->categories->pluck('id'));
        
        // Remove the relationship with the template service
        $company->services()->detach($service->id);
        
        // Create relationship with the new service
        $company->services()->attach($newService->id);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Template service copied successfully.',
            'service' => $newService
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Error copying template service: ' . $e->getMessage()
        ], 500);
    }
}
```

### Frontend Protection

Template services are visually distinguished in the UI with a purple border and a "Template" badge. They can be selected and added to a company like regular services, but they cannot be directly edited or removed from a company once added.

```javascript
// Template services are visually distinguished but can be selected
document.querySelectorAll('.service-card').forEach(card => {
    if (card.dataset.template === '1') {
        // Template services have a purple border and badge
        card.classList.add('border-l-4', 'border-purple-500');
    }
});
```

### UI for Copying Template Services

When a user attempts to edit a template service, they are presented with a modal dialog explaining that template services cannot be directly edited and offering the option to create a copy instead.

```javascript
// Handle edit button click for services
document.querySelectorAll('.edit-service-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const serviceId = this.dataset.serviceId;
        const isTemplate = this.dataset.template === '1';
        
        if (isTemplate) {
            e.preventDefault();
            // Show copy confirmation modal
            showCopyTemplateModal(serviceId);
        }
        // If not a template, normal edit flow continues
    });
});

// Show modal for copying template service
function showCopyTemplateModal(serviceId) {
    const modal = document.getElementById('copy-template-modal');
    const confirmBtn = modal.querySelector('.confirm-copy-btn');
    
    // Set service ID for the confirm button
    confirmBtn.dataset.serviceId = serviceId;
    
    // Show the modal
    modal.classList.remove('hidden');
    
    // Handle confirm button click
    confirmBtn.onclick = function() {
        const serviceId = this.dataset.serviceId;
        copyTemplateService(serviceId);
        modal.classList.add('hidden');
    };
    
    // Handle cancel button click
    modal.querySelector('.cancel-btn').onclick = function() {
        modal.classList.add('hidden');
    };
}

// Copy template service and redirect to edit page for the copy
function copyTemplateService(serviceId) {
    fetch(`/admin/companies/${companyId}/services/${serviceId}/copy`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Template service copied successfully. You can now edit the copy.', 'success');
            // Redirect to edit page for the new service
            window.location.href = `/admin/services/${data.service.id}/edit`;
        } else {
            showToast(data.message || 'Error copying template service', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while copying the template service', 'error');
    });
}
```

## Service Management UI

### Service Selection

The service selection UI is located in `resources/views/admin/services/services.blade.php`. It consists of two main sections:

1. **Selected Services Summary** - Displays the services that are currently selected for the company.
2. **Available Services** - Displays all available services that can be added to the company.

### Adding Services to a Company

Services can be added to a company by checking the checkbox next to the service in the Available Services section. This triggers an AJAX request to add the service to the company.

```javascript
// Handle service selection
function handleServiceSelection(checkbox) {
    const serviceId = checkbox.value;
    const serviceCard = checkbox.closest('.service-card');

    if (checkbox.checked) {
        // Add service to company via AJAX
        fetch(`/admin/companies/${companyId}/services/${serviceId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectedServices.add(serviceId);
                addServiceToSummary(serviceCard);
                // Update the active services count
                updateActiveServicesCount(1);
            } else {
                // Handle error
                showToast(data.message || 'Error adding service', 'error');
                checkbox.checked = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
            checkbox.checked = false;
        });
    } else {
        // Remove service from company
        removeService(serviceId);
    }
}
```

### Removing Services from a Company

Services can be removed from a company by clicking the "Remove" button in the Selected Services Summary section. This triggers a confirmation dialog and then an AJAX request to remove the service from the company.

```javascript
// Remove service from company
function removeService(serviceId) {
    if (confirm('Are you sure you want to remove this service?')) {
        fetch(`/admin/companies/${companyId}/services/${serviceId}/remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectedServices.delete(serviceId);
                removeServiceFromSummary(serviceId);
                // Update the active services count
                updateActiveServicesCount(-1);
            } else {
                showToast(data.message || 'Error removing service', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
}
```

## Routes

The following routes are used for service management:

```php
// Service routes
Route::group(['prefix' => 'services', 'as' => 'services.'], function () {
    Route::get('/', [ServiceController::class, 'index'])->name('index');
    Route::get('/create', [ServiceController::class, 'create'])->name('create');
    Route::post('/', [ServiceController::class, 'store'])->name('store');
    Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
    Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
    Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
});

// Company service routes
Route::group(['prefix' => 'companies/{company}/services', 'as' => 'companies.services.'], function () {
    Route::get('/', [ServiceController::class, 'companyServices'])->name('index');
    Route::post('/{service}', [ServiceController::class, 'addToCompany'])->name('add');
    Route::delete('/{service}/remove', [ServiceController::class, 'removeFromCompany'])->name('remove');
});
```

## Creating New Services

New services can be created by administrators via the service creation form at `/admin/services/create`. The form includes fields for name, description, price, duration, and category.

### Service Creation Process

1. Admin navigates to `/admin/services/create`
2. Admin fills out the service creation form
3. Admin submits the form
4. The `ServiceController@store` method validates the input and creates the service
5. Admin is redirected to the services index page with a success message

## Best Practices

1. **Soft Deletes**: Always use soft deletes when removing services or company-service relationships to maintain data integrity and reporting capabilities.
2. **Template Services**: Use template services for standard services that should be available to all companies and should not be modified or deleted.
3. **Service Categories**: Assign services to appropriate categories to make them easier to find and manage.
4. **Service Pricing**: Set appropriate prices for services based on market research and company needs.
5. **Service Duration**: Set realistic durations for services to ensure proper scheduling.

## Troubleshooting

### Common Issues

1. **Service not appearing in company services**: Check if the service has been soft deleted or if there's an issue with the company-service relationship.
2. **Cannot delete a service**: Check if the service is marked as a template service.
3. **Service not showing up in search**: Check if the service has been categorized correctly and if the search term matches the service name or description.

### Debugging Tips

1. Check the Laravel logs for any errors.
2. Use the browser console to debug JavaScript issues.
3. Verify that the CSRF token is being sent with AJAX requests.
4. Ensure that the service and company IDs are correct in the requests.

## Future Enhancements

1. **Service Restoration**: Add functionality to restore soft-deleted services and company-service relationships.
2. **Bulk Service Management**: Add functionality to add or remove multiple services at once.
3. **Service Analytics**: Add analytics to track service usage and popularity.
4. **Service Variants**: Add support for service variants with different prices and durations.
5. **Service Scheduling**: Integrate services with a scheduling system to allow booking of specific services.

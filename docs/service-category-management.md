# Service Category Management Technical Documentation

## Overview

The Service Category Management system in Faxtina allows administrators to create, edit, delete, and manage service categories for companies. Similar to services, categories can be template-based (immutable) or company-specific (customizable). This document provides technical details on how the service category management system works.

## Database Schema

### Service Categories Table

The service_categories table stores all available service categories in the system.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Name of the category |
| description | text | Description of the category |
| parent_id | integer | ID of the parent category (for hierarchical categories) |
| display_order | integer | Order in which the category is displayed |
| active | boolean | Whether the category is active |
| image_url | string | URL to the category image |
| color | string | Color code for the category |
| slug | string | URL-friendly unique identifier |
| meta_title | string | SEO meta title |
| meta_description | text | SEO meta description |
| meta_keywords | string | SEO meta keywords |
| template | boolean | Whether this is a template category (read-only) |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### Company_Service_Category Pivot Table

This pivot table manages the many-to-many relationship between companies and service categories.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| company_id | bigint | Foreign key to companies table |
| service_category_id | bigint | Foreign key to service_categories table |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

## Soft Delete Implementation

Both the `service_categories` table and the `company_service_category` pivot table implement soft deletes. This means that when a category or a company-category relationship is deleted, it's not actually removed from the database. Instead, the `deleted_at` column is set to the current timestamp.

## Template Categories

Template categories are special categories that are read-only and cannot be deleted. They are identified by the `template` boolean field in the `service_categories` table.

### Backend Protection

The `ServiceCategoryController` includes logic to prevent deletion or modification of template categories:

```php
public function destroy(ServiceCategory $category)
{
    if ($category->template) {
        return redirect()->back()
            ->with('error', 'Template categories cannot be deleted.');
    }
    
    // Delete logic...
}

public function update(Request $request, ServiceCategory $category)
{
    if ($category->template) {
        return redirect()->back()
            ->with('error', 'Template categories cannot be modified.');
    }
    
    // Update logic...
}
```

### Managing Template Categories

There are two ways to work with template categories:

1. **Add Template Categories**: Companies can associate template categories with their business without modifying them. This is useful for standard categories that should remain consistent across all businesses.

2. **Copy to My Categories**: Companies can create editable copies of template categories. This creates a new non-template category that can be customized while maintaining the original template category.

### Copying Template Categories

When a user wants to create an editable copy of a template category, the following process occurs:

```php
public function copyTemplateCategory(Request $request, Company $company, ServiceCategory $category)
{
    // Verify this is a template category
    if (!$category->template) {
        return response()->json([
            'success' => false,
            'message' => 'Only template categories can be copied.'
        ], 400);
    }
    
    DB::beginTransaction();
    
    try {
        // Create a copy of the category
        $newCategory = $category->replicate();
        $newCategory->template = false;
        
        // Generate a unique slug
        $baseSlug = $category->slug;
        $counter = 1;
        $newSlug = $baseSlug;
        
        // Keep trying new slugs until we find a unique one
        while (ServiceCategory::where('slug', $newSlug)->exists()) {
            $newSlug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        $newCategory->slug = $newSlug;
        $newCategory->save();
        
        // Associate the new category with the company
        $company->serviceCategories()->attach($newCategory->id);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Template category copied successfully.',
            'category' => $newCategory
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error copying template category', [
            'category_id' => $category->id,
            'company_id' => $company->id,
            'user_id' => auth()->id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error copying template category: ' . $e->getMessage()
        ], 500);
    }
}
```

## Service Category UI

### Category Listing

The service category listing UI is located in `resources/views/admin/services/categories/index.blade.php`. It includes:

1. **Filtering Options**: Users can filter categories to view all categories, only company categories, or only template categories.
2. **Category List**: Displays all categories with their details and appropriate actions.
3. **Template Category Modal**: Allows users to add template categories to their company.

### Template Category Indicators

Template categories are visually distinguished in the UI with a different background color and a "Template" badge. Their edit and delete buttons are disabled, and they can only be copied or added to the company.

### Adding Template Categories to a Company

The "Add Template Categories" modal allows users to select multiple template categories to add to their company at once. The modal includes:

1. **User Guidance Panel**: Explains how template categories work and how to use them.
2. **Select/Deselect All**: Buttons to quickly select or deselect all available template categories.
3. **Category List**: Shows all template categories with checkboxes, indicating which ones are already added to the company.

## Routes

The following routes are used for service category management:

```php
// Service category routes
Route::group(['prefix' => 'services/categories', 'as' => 'services.categories.'], function () {
    Route::get('/', [ServiceCategoryController::class, 'index'])->name('index');
    Route::get('/create', [ServiceCategoryController::class, 'create'])->name('create');
    Route::post('/', [ServiceCategoryController::class, 'store'])->name('store');
    Route::get('/{category}/edit', [ServiceCategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [ServiceCategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [ServiceCategoryController::class, 'destroy'])->name('destroy');
    Route::post('/add-to-company', [ServiceCategoryController::class, 'addToCompany'])->name('add-to-company');
    Route::post('/{category}/copy', [ServiceCategoryController::class, 'copyTemplateCategory'])->name('copy');
    Route::delete('/{category}/remove-from-company', [ServiceCategoryController::class, 'removeFromCompany'])->name('remove-from-company');
});
```

## Best Practices

1. **Soft Deletes**: Always use soft deletes when removing categories or company-category relationships to maintain data integrity.
2. **Template Categories**: Use template categories for standard categories that should be available to all companies and should not be modified or deleted.
3. **Unique Slugs**: Ensure that all category slugs are unique, especially when copying template categories.
4. **Hierarchical Categories**: Use the parent_id field to create hierarchical category structures when appropriate.

## Troubleshooting

### Common Issues

1. **Duplicate slug error**: When copying a template category, ensure that the system generates a unique slug for the new category.
2. **Cannot edit a category**: Check if the category is marked as a template category.
3. **Category not appearing in company categories**: Check if the category has been soft deleted or if there's an issue with the company-category relationship.

### Debugging Tips

1. Check the Laravel logs for any errors, especially when copying template categories.
2. Use the browser console to debug JavaScript issues in the template category modal.
3. Verify that the CSRF token is being sent with AJAX requests.
4. Ensure that the category and company IDs are correct in the requests.

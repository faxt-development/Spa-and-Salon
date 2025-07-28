# Client Signup & Welcome Email Implementation

## Overview
This implementation provides a complete client registration system with company-scoped welcome emails using EmailCampaign templates from the database.

## Architecture Components

### 1. Client Registration Flow
```
Client Registration Request → Validation → Create Client → Get Welcome Template → Send Email → Response
```

### 2. Key Files

#### Controllers
- `app/Http/Controllers/Api/ClientRegistrationController.php` - Handles client registration and welcome email
- `app/Http/Controllers/Admin/EmailController.php` - Manages email templates (existing)

#### Models
- `app/Models/Client.php` - Client data model with company relationship
- `app/Models/EmailCampaign.php` - Email template storage
- `app/Models/Company.php` - Company data with user relationships

#### Mail
- `app/Mail/ClientWelcomeEmail.php` - Mailable for welcome emails

#### Views
- `resources/views/emails/client-welcome.blade.php` - Email template view

#### Routes
- `routes/api.php` - API endpoint for client registration

### 3. Database Schema

#### Clients Table
```sql
clients (
    id: bigint primary key,
    company_id: bigint foreign key (companies.id),
    first_name: string,
    last_name: string,
    email: string unique,
    phone: string,
    date_of_birth: date,
    address: text,
    marketing_consent: boolean,
    source: string,
    is_guest: boolean,
    timestamps
)
```

#### EmailCampaigns Table
```sql
email_campaigns (
    id: bigint primary key,
    company_id: bigint nullable (null = global template),
    type: enum('welcome', 'reminder', 'campaign'),
    name: string,
    subject: string,
    content: text,
    from_email: string,
    from_name: string,
    is_template: boolean,
    status: enum('active', 'inactive'),
    timestamps
)
```

### 4. Template Selection Logic

The system follows this priority for welcome email templates:
1. **Company-specific template**: `WHERE company_id = ? AND type = 'welcome'`
2. **Global template**: `WHERE company_id IS NULL AND type = 'welcome'`
3. **No template**: Skip email sending

### 5. Placeholder Variables

Available placeholders in EmailCampaign templates:
- `{{client_first_name}}` - Client's first name
- `{{client_last_name}}` - Client's last name
- `{{client_full_name}}` - Client's full name
- `{{client_email}}` - Client's email address
- `{{client_phone}}` - Client's phone number
- `{{company_name}}` - Company/salon name
- `{{company_email}}` - Company email
- `{{company_phone}}` - Company phone
- `{{company_address}}` - Company address
- `{{booking_url}}` - URL for booking appointments
- `{{company_url}}` - Company website URL

### 6. API Usage

#### Client Registration Endpoint
```http
POST /api/clients/register
Content-Type: application/json

{
    "first_name": "Jane",
    "last_name": "Doe",
    "email": "jane@example.com",
    "phone": "555-123-4567",
    "company_id": 1,
    "date_of_birth": "1990-05-15",
    "marketing_consent": true,
    "source": "website"
}
```

#### Response
```json
{
    "status": "success",
    "message": "Client registered successfully",
    "data": {
        "client": { ...client data... },
        "welcome_email_sent": true
    }
}
```

### 7. Error Handling

- **Validation errors**: 422 Unprocessable Entity with field-specific messages
- **Template not found**: Silently skips email sending
- **Email delivery failure**: Logs error but registration succeeds
- **Database errors**: 500 Internal Server Error with generic message

### 8. Security Features

- Input validation and sanitization
- Email uniqueness validation per company
- Company ID validation (must exist)
- Rate limiting on registration endpoint
- CSRF protection for web forms

### 9. Usage Examples

#### Creating a Welcome Template
```php
// Admin creates company-specific welcome template
EmailCampaign::create([
    'company_id' => 1,
    'type' => 'welcome',
    'name' => 'Salon Welcome Email',
    'subject' => 'Welcome to {{company_name}}, {{client_first_name}}!',
    'content' => '<p>Dear {{client_first_name}},</p><p>Welcome to {{company_name}}! We look forward to serving you...</p>',
    'from_email' => 'welcome@salon.com',
    'from_name' => 'Salon Team',
    'is_template' => true,
    'status' => 'active'
]);
```

#### Client Registration with Email
```php
// Client registers through API
$response = Http::post('/api/clients/register', [
    'first_name' => 'John',
    'last_name' => 'Smith',
    'email' => 'john@example.com',
    'company_id' => 1
]);

// System automatically sends welcome email using company template
```

### 10. Testing

Run the welcome email template tests:
```bash
php artisan test tests/Feature/Admin/WelcomeEmailTemplateTest.php
```

### 11. Next Steps

1. Run migrations: `php artisan migrate`
2. Create welcome email templates in admin panel
3. Test client registration flow
4. Set up email queue for better performance
5. Add email delivery tracking
6. Implement template versioning

This implementation provides a complete, scalable solution for client registration with company-scoped welcome emails using the existing EmailCampaign infrastructure.

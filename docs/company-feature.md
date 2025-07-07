# Company Feature Documentation

## Overview
The company feature enables multi-tenant functionality where each company can have its own custom domain, theme, and homepage content. This allows businesses to have branded, white-labeled experiences under their own domains while being powered by a single application instance.

## Key Components

### 1. Company Model
- **Location**: `app/Models/Company.php`
- **Fields**:
  - `domain`: The custom domain for the company (e.g., `company1.example.com`)
  - `is_primary_domain`: Boolean flag indicating if this is the primary domain
  - `homepage_content`: JSON field for storing custom homepage content
  - `theme_settings`: JSON field for storing theme configuration

### 2. Domain Detection
- **Location**: `app/Providers/RouteServiceProvider.php`
- **Behavior**:
  - Automatically detects the requested domain
  - Matches domain against company records
  - Caches company data to minimize database queries
  - Makes company data available throughout the application

### 3. Theme System
- **Location**: `resources/views/company-homepage.blade.php`
- **Features**:
  - Dynamic theming based on company settings
  - Customizable colors, logos, and content
  - Fallback to default theme if no company-specific settings exist

## Implementation Details

### Database Schema
```php
Schema::table('companies', function (Blueprint $table) {
    $table->string('domain')->unique()->nullable();
    $table->boolean('is_primary_domain')->default(false);
    $table->json('homepage_content')->nullable();
    $table->json('theme_settings')->nullable();
});
```

### Accessing Company Data

#### In Controllers
```php
$company = app('currentCompany');
```

#### In Blade Views
```blade
@if(app()->bound('currentCompany'))
    {{-- Access company data --}}
    {{ app('currentCompany')->name }}
@endif
```

#### In Configuration
Theme settings are automatically loaded into the `app.theme` config:
```php
$theme = config('app.theme');
```

## Caching Strategy
- Company data is cached for 24 hours
- Cache key format: `company_theme:{domain}`
- Cache is automatically invalidated when company data is updated

## Setting Up a New Company

1. Add a new company record:
   ```php
   $company = new Company();
   $company->name = 'Example Company';
   $company->domain = 'example.faxtina.com';
   $company->is_primary_domain = true;
   $company->theme_settings = [
       'primary_color' => '#007bff',
       'secondary_color' => '#6c757d',
       'logo_url' => '/storage/company-logos/example.png'
   ];
   $company->save();
   ```

2. Configure DNS:
   - Point the custom domain to your application server
   - Set up SSL certificates (recommended: Let's Encrypt)

## Development Notes

### Local Development
For local testing, add entries to your hosts file:
```
127.0.0.1   example.localhost
127.0.0.1   company1.localhost
```

### Environment Variables
```env
APP_URL=http://localhost:8000
CACHE_DRIVER=file  # or redis/memcached in production
```

## Troubleshooting

### Domain Not Detected
- Verify the domain is correctly set in the companies table
- Check for typos in the domain name
- Ensure the domain is properly configured in your hosts file (for local development)

### Theme Not Loading
- Verify `theme_settings` JSON is valid
- Check browser console for 404 errors on theme assets
- Clear application cache: `php artisan cache:clear`

## Security Considerations
- Always validate domain ownership before associating with a company
- Implement rate limiting on company-specific endpoints
- Regularly audit company data access
- Use HTTPS for all custom domains

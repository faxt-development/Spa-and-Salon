# Theme Implementation Plan

## 1. Database Structure

### Create Themes Table
```php
// database/migrations/[timestamp]_create_themes_table.php
Schema::create('themes', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('primary_color', 7);
    $table->string('secondary_color', 7);
    $table->string('accent_color', 7);
    $table->string('text_color', 7);
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});
```

### Update Companies Table
```php
// database/migrations/[timestamp]_add_theme_id_to_companies_table.php
Schema::table('companies', function (Blueprint $table) {
    $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
});
```

## 2. Models & Relationships

### Theme Model
```php
// app/Models/Theme.php
class Theme extends Model
{
    protected $fillable = [
        'name',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'is_default'
    ];

    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
```

### Company Model Update
```php
// app/Models/Company.php
public function theme()
{
    return $this->belongsTo(Theme::class);
}
```

## 3. Default Theme Seeder

```php
// database/seeders/ThemeSeeder.php
public function run()
{
    Theme::updateOrCreate(
        ['is_default' => true],
        [
            'name' => 'Default Theme',
            'primary_color' => '#3b82f6',
            'secondary_color' => '#64748b',
            'accent_color' => '#8b5cf6',
            'text_color' => '#1e293b',
            'is_default' => true
        ]
    );
}
```

## 4. Theme Service

```php
// app/Services/ThemeService.php
class ThemeService
{
    public function getCurrentTheme()
    {
        if (auth()->check() && $company = auth()->user()->company) {
            return $company->theme ?? $this->getDefaultTheme();
        }
        
        return $this->getDefaultTheme();
    }

    public function getDefaultTheme()
    {
        return Cache::rememberForever('default_theme', function () {
            return Theme::where('is_default', true)->firstOrFail();
        });
    }
}
```

## 5. Theme Middleware

```php
// app/Http/Middleware/ApplyTheme.php
public function handle($request, Closure $next)
{
    $theme = app(ThemeService::class)->getCurrentTheme();
    
    View::share('theme', $theme);
    
    return $next($request);
}
```

## 6. Tailwind Configuration

Update `tailwind.config.js`:
```javascript
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: 'var(--primary-color)',
        secondary: 'var(--secondary-color)',
        accent: 'var(--accent-color)',
        text: 'var(--text-color)',
      },
    },
  },
}
```

## 7. Theme Stylesheet

Create `resources/css/theme.css`:
```css
@layer base {
  :root {
    --primary-color: #3b82f6;
    --secondary-color: #64748b;
    --accent-color: #8b5cf6;
    --text-color: #1e293b;
  }
}
```

## 8. Implementation Steps

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Run seeders:
   ```bash
   php artisan db:seed --class=ThemeSeeder
   ```

3. Register middleware in `app/Http/Kernel.php`

4. Update main layout to include theme styles

5. Test theme application

## 9. Testing Strategy

- Test default theme application for guests
- Test company-specific theme for authenticated users
- Test fallback to default theme
- Test theme caching
- Test theme application in views

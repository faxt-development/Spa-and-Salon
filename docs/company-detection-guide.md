# Company Detection in Faxtina

## Overview

The Faxtina application uses a multi-tenant architecture where different companies can operate on the same platform with their own isolated data and customized experiences. The company detection system is a core feature that determines which company's context should be loaded for a given request.

## How Company Detection Works

The company detection process follows this sequence:

1. **URL-based Detection**: The system first attempts to match the incoming request's domain/hostname against company domains stored in the database.

2. **Request Parameters**: If URL detection fails, the system looks for company identifiers in request parameters (e.g., `?company_id=123`).

3. **Container/Session**: If no company is identified from the URL or request parameters, the system checks if a company is stored in the application container or user session.

4. **Default Company**: If all previous methods fail, the system may fall back to a default company if configured.

## Local Development

When developing locally (e.g., using `php artisan serve`), company detection may fail because:

1. The local URL (e.g., `http://127.0.0.1:8000`) is not registered as a company domain in the database.
2. No company context is available in the session or container.

### Solutions for Local Development

To work around company detection issues in local development:

1. **Add a Local Domain Entry**: Add your local development URL to the `domains` table in the database:

   ```sql
   INSERT INTO domains (domain, company_id, created_at, updated_at)
   VALUES ('127.0.0.1:8000', 1, NOW(), NOW());
   ```

   Replace `1` with the ID of the company you want to use for local development.

2. **Use Query Parameters**: Append `?company_id=X` to your local URLs to explicitly specify which company to use.

3. **Set Default Company**: Configure a default company in your `.env` file:

   ```
   DEFAULT_COMPANY_ID=1
   ```

## Debugging

If you encounter issues with company detection:

1. Check the Laravel logs for debug entries from `HomeController` related to company detection.
2. Verify that the domain you're using is properly registered in the `domains` table.
3. Ensure that the company you're trying to access exists and is active in the database.

## CSRF Protection

The company detection system is closely tied to session management. If company detection fails, you may encounter CSRF token mismatch errors (HTTP 419) because:

1. Sessions may be tied to specific companies
2. Without a valid company context, the session may not initialize correctly
3. This prevents proper CSRF token validation

Always ensure proper company detection is working to avoid CSRF and authentication issues.

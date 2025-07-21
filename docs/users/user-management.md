# User Management

This guide covers all aspects of user management in the Spa & Salon Management Software, including user roles, permissions, profile management, and administrative functions.

## User Roles & Permissions

The system supports several user roles, each with different permissions and access levels:

### Admin Role
Administrators have full access to all system features, including:
- User management (create, edit, delete users)
- Business configuration
- Financial reporting
- System settings
- All staff and client features

### Staff Role
Staff members have access to day-to-day operational features:
- Appointment management
- Client management
- Service delivery
- Point of sale
- Limited reporting

### Client Role
Clients have access to self-service features:
- Personal profile management
- Appointment booking
- Service history
- Loyalty program
- Communication with the business

### Custom Roles
Administrators can create custom roles with specific permission sets for specialized staff positions:
1. Navigate to Settings > User Roles
2. Click "Create New Role"
3. Name the role (e.g., "Receptionist", "Manager")
4. Select permissions for the role
5. Save the new role

## Profile Management

### Updating Your Profile
All users can update their personal information:
1. Click your profile icon in the top-right corner
2. Select "My Profile"
3. Update your information:
   - Name
   - Email
   - Phone number
   - Address
   - Profile picture
4. Click "Save Changes"

### Changing Your Password
1. Go to your profile
2. Click "Security" tab
3. Click "Change Password"
4. Enter your current password
5. Enter and confirm your new password
6. Click "Update Password"

### Communication Preferences
Manage how you receive notifications:
1. Go to your profile
2. Click "Notifications" tab
3. Toggle on/off different notification types:
   - Email notifications
   - SMS notifications
   - Push notifications
   - Marketing communications
4. Click "Save Preferences"

## Admin User Management

### Viewing Users
1. Navigate to Settings > Users
2. View list of all users in the system
3. Filter by:
   - Role
   - Status (Active/Inactive)
   - Registration date
   - Last login date

### Creating New Users
1. Navigate to Settings > Users
2. Click "Add New User"
3. Select user role
4. Enter user details:
   - Name
   - Email
   - Phone number
   - Role
   - Initial password (or select "Send invitation email")
5. Click "Create User"

### Editing Users
1. Navigate to Settings > Users
2. Find the user you want to edit
3. Click the "Edit" button
4. Update user information
5. Click "Save Changes"

### Deactivating Users
Instead of deleting users, deactivate them to preserve history:
1. Navigate to Settings > Users
2. Find the user you want to deactivate
3. Click the "Deactivate" button
4. Confirm deactivation

### Reactivating Users
1. Navigate to Settings > Users
2. Filter to show inactive users
3. Find the user you want to reactivate
4. Click the "Reactivate" button

### Resetting User Passwords
1. Navigate to Settings > Users
2. Find the user who needs a password reset
3. Click the "Reset Password" button
4. Choose to:
   - Set a temporary password
   - Send password reset email
5. Confirm the action

### User Activity Logs
Monitor user actions in the system:
1. Navigate to Settings > User Activity
2. View log of user actions
3. Filter by:
   - User
   - Action type
   - Date range
4. Export logs if needed

## Multi-Company User Management

For users associated with multiple companies:

### Company Switching
1. Click your profile icon in the top-right corner
2. Select "Switch Company"
3. Choose the company you want to switch to
4. The interface will refresh with the selected company's data

### Primary Company Setting
1. Go to your profile
2. Click "Companies" tab
3. View all companies you're associated with
4. Click "Set as Primary" for your main company

### Company-Specific Roles
Users can have different roles in different companies:
1. Admins can assign company-specific roles in Settings > Users
2. Select a user and click "Edit"
3. Under "Company Roles," assign different roles for each company
4. Save changes

## Best Practices for User Management

- Regularly audit user accounts and permissions
- Remove access for staff members who leave the organization
- Use strong passwords and enable two-factor authentication
- Assign the minimum necessary permissions for each role
- Regularly review user activity logs for suspicious behavior
- Create custom roles instead of giving full admin access to multiple users

## Implementation Roadmap

### ✅ Implemented Features

1. **User Roles & Permissions**
   - Admin, Staff, and Client roles are implemented
   - Custom roles can be created and managed
   - Role-based access control is in place

2. **User Management**
   - CRUD operations for users
   - User status management (active/inactive)
   - Password reset functionality
   - User profile management

3. **Multi-Company Support**
   - Users can be associated with multiple companies
   - Primary company designation
   - Company-specific roles

4. **Profile Management**
   - Personal information updates
   - Password changes
   - Communication preferences

### ⚠️ Partially Implemented

1. **User Activity Logs**
   - Basic logging exists but lacks a comprehensive UI
   - Missing filtering and export functionality

2. **Communication Preferences**
   - Basic structure exists but needs more granular controls
   - Missing SMS and push notification integrations

3. **Custom Roles**
   - Role creation exists but lacks advanced permission management
   - Missing role templates for common positions

### ❌ Missing Features

2. **Advanced Security**
   - Two-factor authentication not fully implemented
   - No IP whitelisting or device management
   - Missing session management UI

3. **Bulk Operations**
   - No bulk user import/export
   - Missing bulk user actions (activate/deactivate, assign roles)

4. **Advanced User Fields**
   - No custom fields support
   - Missing user metadata management

5. **Audit Trail**
   - No comprehensive audit logging
   - Missing change history for user records

6. **Self-Service Features**
   - No self-service role requests
   - Missing permission request workflow

7. **API Access Management**
   - No dedicated API key management
   - Missing OAuth client management

### 🔄 Recommended Implementation Order

1. **High Priority**
   - Implement two-factor authentication
   - Add user activity logs with filtering
   - Create bulk import/export functionality

2. **Medium Priority**
   - Enhance communication preferences
   - Implement custom fields support
   - Add audit trail for user changes

3. **Low Priority**
   - Build self-service features
   - Implement advanced role templates
   - Add API access management

4. **Future Enhancements**
   - Advanced reporting on user activity
   - AI-driven user behavior analysis
   - Automated user provisioning/deprovisioning

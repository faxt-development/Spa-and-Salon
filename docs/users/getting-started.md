# Getting Started

This guide will help you get started with the Spa & Salon Management Software, covering system requirements, account registration, and basic login procedures.

## System Requirements

### Browser Requirements
- Google Chrome (latest version recommended)
- Mozilla Firefox (latest version recommended)
- Safari (latest version recommended)
- Microsoft Edge (latest version recommended)

### Device Requirements
- Desktop/Laptop: Windows 10+, macOS 10.14+, or Linux
- Tablet: iPad with iOS 14+ or Android tablet with Android 9+
- Mobile: iPhone with iOS 14+ or Android phone with Android 9+
- Minimum screen resolution: 1280 x 720

### Internet Connection
- Broadband connection with minimum 5 Mbps download speed
- Stable connection for real-time features

## Account Registration

### New Business Registration
1. Visit the [pricing page](https://app.faxtina.com/pricing)
2. Select your desired subscription plan with free trial
3. Complete the checkout process
4. After purchasing, you'll receive an introductory email with a link to begin onboarding
5. Click the link in the email to start the onboarding flow
6. Complete your user profile with:
   - Name
   - Email
   - Password (minimum 8 characters with at least one uppercase letter, one lowercase letter, one number, and one special character)
7. Set up your business profile with:
   - Business name
   - Business address
   - Business type (Spa, Salon, or Both)
   - Number of staff members
   - Operating hours
8. Follow the brief tutorial to learn about key features
9. Access your dashboard where you'll find a link to the onboarding checklist

### Staff Registration
Staff members cannot self-register. They must be invited by an Admin user:
1. Admin creates a staff account and sends an invitation
2. Staff receives an email invitation with a registration link
3. Staff clicks the link and sets up their password
4. Staff completes their profile information

### Client Registration
Clients can register in two ways:

**Self-Registration:**
1. Visit the business's booking page
2. Click "Register" or "Create Account"
3. Complete the registration form with:
   - Name
   - Email
   - Phone number
   - Password
4. Accept the Terms of Service and Privacy Policy
5. Click "Register"
6. Verify email address if required

**Admin/Staff Registration:**
1. Admin/Staff creates a client account
2. System sends an email invitation to the client
3. Client clicks the link and sets up their password
4. Client completes their profile information

## Login & Authentication

### Standard Login
1. Visit your business's login page
2. Enter your email address and password
3. Click "Login"

### Two-Factor Authentication (if enabled)
1. After entering your email and password, you'll be prompted for a verification code
2. Check your email or authentication app for the code
3. Enter the code on the login screen
4. Click "Verify"

### Forgot Password
1. Click "Forgot Password" on the login screen
2. Enter your email address
3. Click "Send Reset Link"
4. Check your email for the password reset link
5. Click the link and follow instructions to create a new password

### Session Management
- Your session will remain active for 2 hours of inactivity
- For security, you'll be automatically logged out after this period
- You can manually log out at any time by clicking your profile icon and selecting "Logout"

### First-Time Login
After completing the initial onboarding process:
1. Your dashboard will include a link to the onboarding checklist
2. The checklist will guide you through essential setup tasks
3. Complete each item on the checklist to fully configure your business
4. Access role-specific tutorials and guides from your dashboard

## Next Steps

After successfully logging in:
- **Admins**: Proceed to [System Administration](./system-administration.md) to set up your business
- **Staff**: Check out the [Staff Dashboard](./dashboard-views.md#staff-dashboard) to familiarize yourself with your workspace
- **Clients**: Visit the [Client Dashboard](./dashboard-views.md#client-dashboard) to book your first appointment

## Implementation Roadmap

The following features mentioned in this document need to be implemented or enhanced:

### Authentication & Registration
- **Two-Factor Authentication (2FA)**: Implement 2FA support for enhanced security
- **Free Trial Registration Flow**: Ensure the subscription-based registration with free trial is fully implemented
- **Post-Purchase Onboarding Email**: Implement automated email with onboarding link after subscription purchase
- **Staff Registration & Invitation**: Implement staff invitation system and registration workflow
- **Client Self-Registration**: Add client self-registration functionality on booking page

### User Experience
- **Onboarding Tutorial**: Create guided tutorial for first-time users after registration
- **Onboarding Checklist Integration**: Ensure dashboard properly links to onboarding checklist
- **System Requirements Verification**: Add browser/device compatibility checks

### Documentation
- **Pricing Page Documentation**: Update documentation to accurately reflect the pricing page registration flow
- **Onboarding Flow Documentation**: Ensure documentation accurately describes the complete onboarding experience

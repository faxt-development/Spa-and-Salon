# Client Forms & Intake

This guide covers all aspects of client forms and intake processes in the Spa & Salon Management Software, including creating custom forms, managing client intake, and handling consent documentation.

## Form Builder

### Creating Custom Forms
Design forms for various purposes:
1. Navigate to Settings > Forms > Form Builder
2. Click "Create New Form"
3. Enter form details:
   - Form name
   - Description
   - Purpose (intake, consent, waiver, etc.)
   - Required status
4. Add form fields:
   - Text fields (short and long)
   - Multiple choice questions
   - Checkboxes
   - Dropdown menus
   - Date selectors
   - File uploads
   - Signature fields
   - Rating scales
5. Configure field properties:
   - Required vs. optional
   - Field validation
   - Help text
   - Default values
6. Arrange field order using drag-and-drop
7. Preview form
8. Save and publish

### Form Templates
Use and customize pre-built templates:
1. Navigate to Settings > Forms > Templates
2. Browse available templates:
   - New client intake
   - Health history
   - Service consent
   - COVID-19 screening
   - Satisfaction survey
   - Treatment feedback
3. Select a template
4. Customize as needed
5. Save as a new form

### Form Logic
Create dynamic, conditional forms:
1. Navigate to Settings > Forms > Form Builder
2. Select a form to edit
3. Click "Add Logic"
4. Configure conditional logic:
   - If [field] [condition] [value], then:
     - Show/hide specific fields
     - Make fields required/optional
     - Set field values
     - Skip to section
5. Test logic flow
6. Save changes

## Client Intake Process

### Guest Client Intake
Process clients who book as guests:
1. When a guest books an appointment online, a guest client record is automatically created
2. Guest clients are flagged with the `is_guest` status in the system
3. Guest client records include:
   - First and last name
   - Email address
   - Phone number (if provided)
   - Marketing consent status
4. After their appointment, guest clients can be:
   - Converted to regular clients
   - Merged with existing client records if duplicates are found
   - Retained as guest clients for future bookings
5. Access guest client records under Clients > Guest Clients

### New Client Intake
Streamline the onboarding process:
1. Navigate to Clients > New Client
2. Enter basic client information:
   - Name
   - Contact information
   - Date of birth
3. Select appropriate intake forms
4. Choose intake method:
   - Send forms via email for completion before appointment
   - Complete forms in-office on tablet
   - Print forms for manual completion
5. Track form completion status
6. Send reminders for incomplete forms

### Digital Form Completion
Guide clients through electronic forms:
1. Client receives email with secure form link
2. Client clicks link and verifies identity
3. Client completes form sections:
   - Personal information
   - Medical history
   - Service preferences
   - Consent agreements
4. Client electronically signs forms
5. Client submits completed forms
6. System notifies staff of form completion
7. Form data is automatically added to client profile

### In-Office Form Completion
Assist clients with on-site forms:
1. Navigate to Clients > Check-In
2. Select client
3. Click "Start Intake Process"
4. Hand tablet to client or assist with form completion
5. Forms are presented in logical sequence
6. Client completes and signs forms
7. Submit completed forms
8. Form data is instantly available to staff

## Form Management

### Assigning Forms to Services
Link required forms to specific services:
1. Navigate to Services > All Services
2. Select a service to edit
3. Go to "Required Forms" tab
4. Select forms required for this service:
   - Intake forms
   - Consent forms
   - Waivers
   - Aftercare instructions
5. Set when forms should be completed:
   - Before appointment
   - At check-in
   - After service
6. Save changes
7. System will automatically request these forms when service is booked

### Form Expiration Settings
Configure form renewal requirements:
1. Navigate to Settings > Forms > Expiration
2. Select a form
3. Configure expiration settings:
   - No expiration
   - Expire after specific time period
   - Expire on specific date
   - Require renewal for each visit
4. Set renewal notification settings
5. Save changes
6. System will prompt for form renewal when needed

### Form Version Control
Manage form updates and changes:
1. Navigate to Settings > Forms > Version History
2. Select a form
3. View version history:
   - Creation date
   - Modification dates
   - Changes made
   - Author
4. Compare versions
5. Restore previous versions if needed
6. Set policy for existing submissions:
   - Keep under original version
   - Request update to new version
   - Flag for review

## Consent Management

### Service Consent Forms
Manage service-specific consent:
1. Navigate to Settings > Forms > Consent Forms
2. Click "Create Consent Form"
3. Configure form details:
   - Service category
   - Required disclosures
   - Risks and benefits
   - Alternative treatments
   - Expected outcomes
4. Add signature fields:
   - Client signature
   - Staff witness
   - Date and time stamps
5. Save and assign to relevant services

### Consent Tracking
Monitor client consent status:
1. Open client profile
2. Navigate to "Consent & Forms" tab
3. View all consent documents:
   - Date signed
   - Expiration date
   - Services covered
   - Form version
4. Filter by status:
   - Active
   - Expired
   - Pending
5. Send renewal requests for expired consents

### Photo Consent
Manage image usage permissions:
1. Navigate to Settings > Forms > Photo Consent
2. Configure photo consent options:
   - Before/after photos for medical record only
   - Internal training use
   - Marketing use
   - Social media use
3. Create consent form with clear options
4. Track photo consent status for each client
5. Filter client photos based on consent level

## Medical Intake

### Health History Forms
Collect and manage medical information:
1. Navigate to Settings > Forms > Health History
2. Configure health history form:
   - Medical conditions
   - Medications
   - Allergies
   - Previous treatments
   - Contraindications
3. Set up alerts for critical conditions
4. Configure privacy settings
5. Create update reminders

### Medical Alerts
Set up safety notifications:
1. Navigate to Settings > Forms > Medical Alerts
2. Configure alert triggers:
   - Specific medical conditions
   - Medication interactions
   - Allergies
   - Contraindications for services
3. Set alert display settings:
   - Visual indicators
   - Pop-up warnings
   - Required acknowledgment
4. Test alert system
5. Train staff on alert protocols

### HIPAA Compliance
Ensure medical information security:
1. Navigate to Settings > Privacy > HIPAA Settings
2. Configure compliance settings:
   - Access controls
   - Audit trails
   - Data encryption
   - Retention policies
3. Set up compliance documentation
4. Configure client authorization forms
5. Establish secure communication channels

## Form Data Management

### Data Integration
Connect form data to client profiles:
1. Navigate to Settings > Forms > Data Mapping
2. Select a form
3. Map form fields to client database fields:
   - Contact information
   - Preferences
   - Medical history
   - Service history
4. Configure update behavior:
   - Always update
   - Update if empty
   - Ask before updating
   - Keep history of changes
5. Save mapping configuration

### Form Analytics
Track form completion metrics:
1. Navigate to Reports > Forms
2. View form statistics:
   - Completion rates
   - Average completion time
   - Abandonment points
   - Common responses
   - Submission methods
3. Identify opportunities for form improvement
4. Generate compliance reports

### Data Export
Extract form submission data:
1. Navigate to Forms > Export
2. Select forms to export
3. Choose date range
4. Select export format:
   - CSV
   - Excel
   - PDF
   - JSON
5. Configure field inclusion
6. Generate and download export file

## Client Portal Forms

### Client Self-Service
Enable clients to manage their own forms:
1. Client logs into portal
2. Navigates to "My Forms"
3. Views:
   - Required forms
   - Completed forms
   - Forms needing updates
   - Form history
4. Completes or updates forms as needed
5. Receives confirmation of submission

### Pre-Appointment Forms
Streamline check-in process:
1. System automatically sends required forms before appointment
2. Client receives email notification
3. Client completes forms online
4. Staff receives notification of completion
5. Check-in process is expedited
6. Service provider reviews completed forms before appointment

### Form Accessibility
Ensure forms are available to all clients:
1. Navigate to Settings > Forms > Accessibility
2. Configure accessibility options:
   - Screen reader compatibility
   - Keyboard navigation
   - Text size options
   - High contrast mode
   - Language translations
3. Test with accessibility tools
4. Provide alternative formats when needed

## Best Practices for Client Forms & Intake

- Keep forms concise and only ask for essential information
- Break long forms into logical sections with progress indicators
- Use conditional logic to show only relevant questions
- Ensure forms are mobile-friendly for completion on any device
- Regularly review and update form content for accuracy
- Set up automatic reminders for clients with incomplete forms
- Train staff on handling sensitive information in forms
- Create clear instructions for each form section
- Use plain language and avoid industry jargon
- Regularly audit form completion rates and optimize problematic sections
- Ensure all forms comply with relevant regulations (HIPAA, GDPR, etc.)
- Provide estimated completion time at the beginning of each form

## Implementation Roadmap

Based on a comprehensive audit of the codebase, the following features need to be implemented:

### Features Missing from the Codebase

1. **Form Builder System**
   - No form builder models, controllers, or views found
   - Missing form field types and configurations
   - No form logic/conditional functionality

2. **Form Templates**
   - No template system for pre-built forms
   - Missing template customization functionality

3. **Client Intake Process**
   - Basic client creation exists, but no dedicated intake form system
   - No digital form completion workflow
   - No in-office form completion on tablets

4. **Form Management**
   - No system to assign forms to services
   - Missing form expiration settings
   - No version control for forms

5. **Consent Management**
   - No service consent forms
   - Missing consent tracking
   - No photo consent management

6. **Medical Intake**
   - No health history forms
   - Missing medical alerts system
   - No HIPAA compliance settings

7. **Form Data Management**
   - No data integration between forms and client profiles
   - Missing form analytics
   - No data export functionality

8. **Client Portal Forms**
   - No client self-service for forms
   - Missing pre-appointment form system
   - No form accessibility options

### Implementation Plan

To implement these features, we need to:

1. Create database migrations for:
   - Forms table (for form definitions)
   - Form fields table (for field definitions)
   - Form submissions table (for completed forms)
   - Form templates table
   - Form logic/conditions table
   - Consent records table

2. Develop models for:
   - Form
   - FormField
   - FormSubmission
   - FormTemplate
   - FormLogic
   - ConsentRecord

3. Create controllers for:
   - FormBuilderController
   - FormTemplateController
   - ClientIntakeController
   - ConsentManagementController
   - FormDataController

4. Build views for:
   - Form builder interface
   - Form template management
   - Client intake process
   - Digital form completion
   - Form management
   - Consent tracking
   - Form analytics

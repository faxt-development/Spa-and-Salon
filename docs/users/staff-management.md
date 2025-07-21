# Staff Management

This guide covers all aspects of staff management in the Spa & Salon Management Software, including creating and managing staff profiles, scheduling, and performance tracking.

## Staff Profiles

### Creating Staff Profiles
1. Navigate to Staff > All Staff
2. Click "Add New Staff" button
3. Enter staff information:
   - First and last name (required)
   - Email address (required)
   - Phone number (required)
   - Position/title (required)
   - Staff level (junior, senior, master, etc.)
   - Profile photo (recommended)
   - Bio/description (for online booking)
4. Set system access:
   - User role (Admin, Staff, Limited Access)
   - Login credentials
   - Permission groups
5. Click "Create Staff"

### Editing Staff Information
1. Navigate to Staff > All Staff
2. Find the staff member you want to edit
3. Click the "Edit" button
4. Update staff information
5. Click "Save Changes"

### Staff Profile Sections
A complete staff profile includes:
- **Personal Information**: Contact details and employment information
- **Services**: Services this staff member can perform
- **Schedule**: Working hours and availability
- **Performance**: Metrics and statistics
- **Commission**: Commission rates and earnings
- **Documents**: Certifications and employment documents
- **Notes**: Administrative notes about the staff member

### Staff Service Assignments
Specify which services each staff member can perform:
1. Open a staff profile
2. Navigate to the "Services" tab
3. Click "Assign Services"
4. Select services this staff member can perform
5. Set staff-specific pricing if applicable
6. Set staff-specific duration if applicable
7. Click "Save Assignments"

## Staff Scheduling

### Setting Staff Availability
Define when staff members are available to work:
1. Navigate to Staff > Availability
2. Select a staff member
3. Set regular working hours:
   - Select days of the week
   - Set start and end times for each day
   - Create multiple shifts if needed
4. Save the schedule

### Managing Time Off
Record staff vacation and time off:
1. Navigate to Staff > Time Off
2. Click "Add Time Off" button
3. Select staff member
4. Choose time off type:
   - Vacation
   - Sick leave
   - Personal day
   - Training
5. Set date range and times
6. Add notes if needed
7. Click "Save"
8. The system will automatically block this time in the scheduling calendar

### Viewing Staff Schedules
See when staff members are working:
1. Navigate to Calendar
2. Filter view by staff member
3. Toggle between day, week, or month view
4. View color-coded schedule:
   - Appointments
   - Breaks
   - Time off
   - Available time

### Break Scheduling
Add breaks to staff schedules:
1. Navigate to Staff > Breaks
2. Select staff member
3. Add regular breaks:
   - Lunch breaks
   - Short breaks
   - Preparation time
4. Set duration and timing
5. Save break schedule

## Staff Performance & Metrics

### Performance Dashboard
View key performance indicators:
1. Navigate to Staff > Performance
2. Select staff member and date range
3. View performance metrics:
   - Total appointments completed
   - Revenue generated
   - Client retention rate
   - Rebooking rate
   - Average service rating
   - Retail sales

### Commission Tracking
Monitor staff earnings:
1. Navigate to Staff > Commissions
2. Select staff member and date range
3. View commission breakdown:
   - Service commissions
   - Product sales commissions
   - Total earnings
   - Historical earnings trends

### Setting Commission Rates
Configure how staff are compensated:
1. Navigate to Settings > Commissions
2. Set default commission structure:
   - Percentage of service revenue
   - Tiered commission rates
   - Product sales commission
3. Create staff-specific commission rules:
   - Select staff member
   - Override default rates if needed
   - Set service-specific rates if needed
4. Save commission settings

### Staff Utilization Reports
Monitor how efficiently staff time is used:
1. Navigate to Reports > Staff Utilization
2. Select date range and staff members
3. View utilization metrics:
   - Booked vs. available hours
   - Average service duration
   - Gaps between appointments
   - Revenue per hour

## Staff Communication

### Staff Notifications
Configure how staff receive system notifications:
1. Navigate to Staff > Notifications
2. Select staff member
3. Configure notification preferences:
   - New appointment alerts
   - Schedule changes
   - Client arrivals
   - Administrative announcements
4. Set notification methods:
   - In-app notifications
   - Email
   - SMS
5. Save preferences

### Staff Message Board
Communicate with the team:
1. Navigate to Staff > Message Board
2. Create new announcement:
   - Enter message title and content
   - Set visibility (all staff or specific members)
   - Add attachments if needed
   - Set expiration date if applicable
3. Post announcement
4. Staff will see announcements when they log in

### Direct Messaging
Send messages to individual staff members:
1. Open a staff profile
2. Click "Send Message" button
3. Enter message content
4. Select message priority
5. Click "Send"
6. Message will appear in staff member's notification center

## Staff Training & Development

### Skill Tracking
Record staff certifications and skills:
1. Open a staff profile
2. Navigate to "Skills & Certifications" tab
3. Click "Add Skill/Certification"
4. Enter details:
   - Skill/certification name
   - Date acquired
   - Expiration date (if applicable)
   - Attach documentation
5. Save the entry

### Training Management
Schedule and track staff training:
1. Navigate to Staff > Training
2. Click "Schedule Training"
3. Enter training details:
   - Training name and description
   - Date and time
   - Location (in-person or online)
   - Required attendees
4. Save the training event
5. System will notify staff and block their schedule

## Multi-Location Staff Management

### Location Assignments
Manage staff across multiple locations:
1. Open a staff profile
2. Navigate to "Locations" tab
3. Select locations where this staff member works
4. Set primary location
5. Configure location-specific schedules
6. Save changes

### Staff Transfers
Move staff between locations:
1. Open a staff profile
2. Navigate to "Locations" tab
3. Click "Transfer" button
4. Select new primary location
5. Set effective date
6. Update schedule for new location
7. Save changes

## Best Practices for Staff Management

- Regularly update staff profiles with new skills and certifications
- Ensure staff schedules are accurate to prevent booking conflicts
- Review performance metrics with staff regularly
- Keep commission structures transparent and easy to understand
- Use the staff message board for important announcements
- Train staff on using the system to view their schedule and performance
- Regularly audit staff permissions to ensure appropriate access levels
- Maintain accurate contact information for emergency communications

## Implementation Roadmap

### 1. Staff Profiles (Partially Implemented)
**Implemented:**
- Basic staff profile fields (name, email, phone, position)
- Profile photo and bio support
- Active/inactive status
- Work schedule (start/end times, work days)

**Missing Features:**
- Staff level (junior, senior, master, etc.) - needs to be added to the staff table
- System access management (user roles and permissions) - needs integration with staff profiles
- UI for managing staff profiles in the admin panel

### 2. Staff Scheduling (Partially Implemented)
**Implemented:**
- Basic work schedule (start/end times, work days)
- Availability calculation methods
- Appointment scheduling with conflict detection

**Missing Features:**
- Time off management (vacation, sick leave, personal days)
- Break scheduling
- Visual calendar interface for managing schedules
- Recurring schedule patterns
- Schedule templates

### 3. Staff Performance & Metrics (Partially Implemented)
**Implemented:**
- Performance metrics model and migration
- Basic utilization calculation
- Revenue and commission tracking

**Missing Features:**
- Performance dashboard UI
- Commission rate management interface
- Staff utilization reports
- Client retention and rebooking rate tracking
- Retail sales tracking

### 4. Staff Communication (Not Implemented)
**Missing Features:**
- Staff notification preferences
- In-app message board/announcements
- Direct messaging between staff
- Notification system for appointments, schedule changes
- Client arrival notifications

### 5. Training & Development (Not Implemented)
**Missing Features:**
- Skill and certification tracking
- Training management system
- Document storage for certifications
- Training completion tracking
- Skill matrix view

### 6. Multi-Location Support (Not Implemented)
**Missing Features:**
- Location assignments for staff
- Staff transfer functionality between locations
- Location-specific schedules
- Location-based availability

### 7. API Endpoints (Partially Implemented)
**Implemented:**
- Basic CRUD operations in StaffController

**Missing Endpoints:**
- Availability management
- Time off requests
- Performance metrics
- Commission calculations
- Communication features

### 8. Frontend Components (Mostly Missing)
**Missing:**
- Staff management dashboard
- Schedule management interface
- Performance reporting UI
- Communication center
- Training and certification management

### 9. Integration Points (Partially Implemented)
**Implemented:**
- Basic integration with appointments
- Basic integration with services

**Needed:**
- Integration with user authentication/roles
- Calendar system integration
- Notification system integration
- Document management for certifications

### 10. Reporting (Mostly Missing)
**Missing:**
- Staff performance reports
- Commission reports
- Utilization reports
- Attendance reports
- Training completion reports

### Priority Recommendations:
1. **High Priority:**
   - Complete staff profile management
   - Implement time off and break scheduling
   - Build the staff dashboard with key metrics
   - Add notification preferences

2. **Medium Priority:**
   - Implement communication features
   - Add training and certification tracking
   - Build reporting functionality

3. **Lower Priority:**
   - Multi-location support
   - Advanced scheduling features
   - Advanced reporting

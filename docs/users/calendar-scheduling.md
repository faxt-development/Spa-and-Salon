# Calendar & Scheduling

This guide covers all aspects of calendar management and scheduling in the Spa & Salon Management Software, including calendar views, scheduling appointments, and managing staff availability.

## Calendar Views

### Main Calendar Interface
Navigate and understand the calendar:
1. Access the calendar by clicking "Calendar" in the main navigation
2. Choose your preferred view:
   - Day view: Detailed hour-by-hour schedule for a single day
   - Week view: Overview of the entire week's schedule
   - Month view: High-level view of appointments across a month
   - List view: Text-based list of upcoming appointments
3. Use the date picker to navigate to specific dates
4. Use the refresh button to update the calendar with latest changes

### Calendar Filters
Customize what you see in the calendar:
1. Click the "Filter" button in the calendar toolbar
2. Filter options include:
   - Staff members
   - Service types
   - Rooms/stations
   - Appointment status
   - Client type (new/returning)
3. Apply multiple filters simultaneously
4. Save filter combinations for quick access
5. Clear all filters to return to the default view

### Color Coding
Understand calendar color indicators:
- **Blue**: Confirmed appointments
- **Yellow**: Tentative/pending appointments
- **Green**: Completed appointments
- **Red**: Cancelled appointments
- **Purple**: Blocked time (non-appointment)
- **Gray**: Staff unavailable/time off

### Calendar Synchronization
Connect with external calendars:
1. Navigate to Settings > Calendar > Synchronization
2. Connect external calendar services:
   - Google Calendar
   - Microsoft Outlook
   - Apple iCalendar
   - Other iCal-compatible calendars
3. Configure sync settings:
   - One-way or two-way sync
   - Appointment details to include
   - Privacy settings
4. Set sync frequency
5. Test synchronization

## Appointment Scheduling

### Creating Appointments
Schedule new appointments:
1. Click the "+" button or click directly on the desired time slot
2. Select appointment type:
   - Standard appointment
   - Group appointment
   - Recurring appointment
3. Enter appointment details:
   - Client (select existing or create new)
   - Service(s)
   - Staff member
   - Date and time
   - Duration (auto-calculated based on services)
   - Room/station (if applicable)
4. Add notes or special instructions
5. Set appointment status (confirmed, tentative)
6. Click "Save Appointment"

### Quick Booking
Rapidly schedule simple appointments:
1. Click "Quick Book" in the calendar toolbar
2. Enter minimal required information:
   - Client name
   - Service
   - Staff member
   - Date and time
3. System automatically calculates duration and finds available slot
4. Click "Book" to create the appointment
5. Edit additional details later if needed

### Group Appointments
Schedule services for multiple clients:
1. Click "Group Appointment" in the calendar
2. Select service type suitable for groups
3. Set maximum group size
4. Choose date, time, and duration
5. Assign staff member(s)
6. Add clients to the group:
   - Add from existing client list
   - Allow clients to self-book into group
7. Set individual arrival times if needed
8. Save group appointment

### Recurring Appointments
Set up repeating appointments:
1. When creating an appointment, check "Recurring" option
2. Configure recurrence pattern:
   - Frequency (daily, weekly, monthly)
   - Interval (every 1 week, 2 weeks, etc.)
   - Day of week selection
   - End date or number of occurrences
3. Review generated appointment dates
4. Adjust individual instances if needed
5. Save recurring series
6. Manage as series or individual appointments

## Appointment Management

### Editing Appointments
Modify existing appointments:
1. Click on the appointment in the calendar
2. Click "Edit" button
3. Modify any appointment details:
   - Date and time
   - Duration
   - Services
   - Staff member
   - Room/station
   - Notes
4. For recurring appointments, choose:
   - Edit this occurrence only
   - Edit all future occurrences
   - Edit entire series
5. Save changes
6. System updates client notifications if configured

### Rescheduling
Move appointments to a new time:
1. Method 1: Drag and drop the appointment to a new time slot
2. Method 2: Right-click appointment and select "Reschedule"
3. Select new date and time
4. Verify staff and room availability
5. Confirm the change
6. System sends rescheduling notification to client

### Cancelling Appointments
Process appointment cancellations:
1. Click on the appointment in the calendar
2. Click "Cancel" button
3. Select cancellation reason from dropdown
4. Add additional notes if needed
5. Choose whether to:
   - Charge cancellation fee
   - Waive cancellation fee
   - Add to no-show list
6. Confirm cancellation
7. System sends cancellation notification if configured

### Appointment Check-in
Process client arrivals:
1. Navigate to the "Today's Appointments" view
2. Find the client's appointment
3. Click "Check In" button
4. Record arrival time (automatic or manual)
5. Update appointment status to "In Progress"
6. Notify service provider of client arrival
7. Direct client to waiting area or treatment room

### Appointment Completion
Finalize completed appointments:
1. Click on the in-progress appointment
2. Click "Complete" button
3. Record completion time
4. Add service notes if needed
5. Prompt for follow-up booking
6. Direct client to checkout process
7. Update appointment status to "Completed"

## Staff Scheduling

### Staff Availability
Set regular working hours:
1. Navigate to Staff > Availability
2. Select staff member
3. Set regular working schedule:
   - Working days
   - Start and end times
   - Lunch/break times
4. Set recurring time off:
   - Regular days off
   - Half days
5. Save availability settings
6. System prevents booking outside available hours

### Time Off Requests
Manage staff absences:
1. Staff member navigates to "My Schedule" > "Request Time Off"
2. Enters request details:
   - Date(s)
   - Full or partial day
   - Reason
   - Notes
3. Submits request
4. Manager receives notification
5. Manager approves or denies request
6. Staff schedule is updated accordingly
7. Existing appointments are flagged for rescheduling

### Staff Rotation
Configure rotating schedules:
1. Navigate to Settings > Staff > Rotation
2. Create rotation pattern:
   - Define rotation period (weekly, bi-weekly, etc.)
   - Set different schedules for each rotation
   - Assign staff to rotation positions
3. Set rotation start date
4. System automatically updates staff schedules
5. View rotation calendar to verify assignments

### Breaks and Preparation Time
Schedule non-appointment time:
1. Navigate to Settings > Calendar > Break Settings
2. Configure default break times:
   - Between appointments
   - For lunch
   - For setup/cleanup
3. Set service-specific preparation times
4. System automatically blocks appropriate time
5. Breaks appear in staff schedules

## Advanced Scheduling Features

### Waitlist Management
Handle fully-booked situations:
1. When desired time is unavailable, click "Add to Waitlist"
2. Enter waitlist request:
   - Client
   - Service
   - Preferred staff
   - Desired date/time range
   - Priority level
3. If cancellation occurs, system suggests waitlisted clients
4. Notify clients of availability automatically or manually
5. Convert waitlist entry to appointment

### Double-Booking Protection
Prevent scheduling conflicts:
1. System automatically checks for conflicts when booking:
   - Staff availability
   - Room/equipment availability
   - Client existing appointments
2. Warning appears if conflict detected
3. Options to resolve conflict:
   - Choose different time
   - Choose different staff
   - Choose different room
   - Override warning (admin only)

### Schedule Optimization
Maximize efficiency in scheduling:
1. Navigate to Calendar > Optimize
2. System analyzes current schedule
3. Suggests improvements:
   - Grouping similar services
   - Minimizing gaps between appointments
   - Balancing staff workload
   - Optimizing room usage
4. Review suggestions
5. Apply selected optimizations
6. Confirm changes

### Resource Scheduling
Manage rooms and equipment:
1. Navigate to Calendar > Resources
2. View resource availability:
   - Treatment rooms
   - Styling stations
   - Specialized equipment
3. Schedule resources independently or with appointments
4. Block resources for maintenance
5. View resource utilization reports

## Calendar Settings

### Business Hours
Configure when your business operates:
1. Navigate to Settings > Calendar > Business Hours
2. Set regular hours of operation:
   - Opening and closing times for each day
   - Special hours for holidays
   - Seasonal hour adjustments
3. Set scheduling rules:
   - Earliest booking time
   - Latest booking time
   - Minimum advance notice
4. Save business hour settings

### Appointment Buffers
Configure spacing between appointments:
1. Navigate to Settings > Calendar > Buffers
2. Set default buffer times:
   - Before appointments
   - After appointments
   - Between specific service types
3. Configure service-specific buffers
4. Set staff-specific buffer requirements
5. System automatically applies buffers when scheduling

### Calendar Permissions
Control who can view and edit schedules:
1. Navigate to Settings > Calendar > Permissions
2. Configure role-based permissions:
   - View all appointments
   - View specific staff appointments
   - Create appointments
   - Edit appointments
   - Cancel appointments
   - Override booking rules
3. Set individual staff permissions
4. Save permission settings

### Booking Rules
Establish scheduling policies:
1. Navigate to Settings > Calendar > Booking Rules
2. Configure booking restrictions:
   - Minimum/maximum advance booking
   - Same-day booking cutoff time
   - Maximum appointments per day/week
   - Required deposit for booking
   - Cancellation policy timeframes
3. Set service-specific booking rules
4. Configure client type rules (new vs. existing)
5. Save booking rule configuration

## Best Practices for Calendar & Scheduling

- Review the next day's schedule at the end of each business day
- Configure appropriate buffer times between appointments
- Use color coding consistently to quickly identify appointment status
- Train all staff on proper scheduling procedures
- Regularly optimize schedules to minimize gaps
- Set up automatic reminders to reduce no-shows
- Use the waitlist feature for popular time slots
- Maintain accurate service durations to prevent running behind
- Schedule staff breaks to prevent burnout
- Use recurring appointments for regular clients
- Keep notes on client preferences for scheduling
- Regularly review scheduling analytics to identify improvement opportunities


## Roadmap

### Features Present in the Current Codebase

#### Appointment Management
1. **Creating Appointments**
   - Basic appointment creation with client, staff, service selection
   - Date and time selection
   - Notes field
   - Price calculation based on services

2. **Editing Appointments**
   - Updating appointment details (client, staff, services, time)
   - Status updates (scheduled, confirmed, completed, cancelled)

3. **Cancelling Appointments**
   - Appointment cancellation functionality
   - Cancellation reason tracking

4. **Staff Availability Management**
   - Setting regular working hours (work_days, work_start_time, work_end_time)
   - Staff availability checking before booking

### Features Missing or Incomplete

#### Calendar Views
1. **Main Calendar Interface**
   - No dedicated calendar view implementation (day, week, month, list views)
   - No date picker for calendar navigation

2. **Calendar Filters**
   - No filtering system for appointments by staff, service, status, etc.
   - No saved filter combinations

3. **Color Coding**
   - No color coding system for appointment status

4. **Calendar Synchronization**
   - No integration with external calendars (Google, Outlook, Apple)

#### Advanced Appointment Features
1. **Group Appointments**
   - No support for booking multiple clients into the same appointment

2. **Recurring Appointments**
   - No functionality for setting up repeating appointments

3. **Quick Booking**
   - No streamlined quick booking interface

4. **Appointment Check-in**
   - No client check-in process

#### Staff Scheduling
1. **Time Off Requests**
   - No system for staff to request time off
   - No manager approval workflow

2. **Staff Rotation**
   - No rotation pattern configuration

3. **Breaks and Preparation Time**
   - No automatic break scheduling between appointments
   - No service-specific preparation time

#### Advanced Scheduling Features
1. **Waitlist Management**
   - No waitlist functionality for fully-booked situations

2. **Schedule Optimization**
   - No tools for optimizing the schedule

3. **Resource Scheduling**
   - No room/equipment scheduling

#### Calendar Settings
1. **Business Hours**
   - No business-wide hours configuration

2. **Appointment Buffers**
   - No buffer time configuration between appointments

3. **Calendar Permissions**
   - Basic role-based permissions exist but not specific to calendar views

4. **Booking Rules**
   - No advanced booking rules (minimum advance notice, etc.)

### Recommendations for Implementation

#### Priority 1: Core Calendar Functionality
- Implement a proper calendar view with day/week/month/list options
- Add color coding for appointment status
- Implement calendar filtering by staff, service, and status

#### Priority 2: Advanced Appointment Features
- Add recurring appointment functionality
- Implement group appointment booking
- Add appointment check-in and completion workflow

#### Priority 3: Staff Scheduling Enhancements
- Implement time off request system
- Add break and preparation time configuration
- Develop staff rotation scheduling

#### Priority 4: Resource and Settings
- Implement room/equipment scheduling
- Add business hours configuration
- Develop appointment buffer settings
- Create waitlist management system


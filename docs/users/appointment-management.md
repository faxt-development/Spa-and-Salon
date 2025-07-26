# Appointment Management

This guide covers all aspects of appointment management in the Spa & Salon Management Software, including viewing, creating, editing, and managing appointments for both staff and clients.

## Viewing Appointments

### Calendar Views
The system offers multiple ways to view appointments:

**Daily View**
1. Navigate to Appointments > Calendar
2. Select "Day" view from the top navigation
3. Use the date picker to select a specific date
4. View all appointments scheduled for that day
5. Color coding indicates appointment status (confirmed, in progress, completed, etc.)

**Weekly View**
1. Navigate to Appointments > Calendar
2. Select "Week" view from the top navigation
3. View appointments for the entire week
4. Scroll horizontally to see all staff members
5. Use the week selector to navigate between weeks

**Monthly View**
1. Navigate to Appointments > Calendar
2. Select "Month" view from the top navigation
3. See an overview of all appointments for the month
4. Click on any day to see detailed appointments

### Filtering Appointments
Refine your appointment view:
1. Use the filter panel on the left side of the calendar
2. Filter by:
   - Staff member
   - Service type
   - Status
   - Client
   - Room/location
3. Apply multiple filters for precise results
4. Save common filter combinations as presets

### Appointment Details
View detailed information about any appointment:
1. Click on an appointment in the calendar
2. View the appointment details panel:
   - Client information
   - Service details
   - Duration and time
   - Staff assigned
   - Room/equipment needed
   - Special notes
   - Payment status

## Creating Appointments

### Guest Appointment Booking
Guests can book appointments without creating an account first:

1. Guests access the booking system through the "Book Now" button on the website
2. They select desired service(s) from the available options
3. They choose a date for their appointment
4. Available time slots and staff members are displayed
5. They select their preferred time and staff member
6. They enter their contact information (name, email, phone)
7. They confirm their booking
8. A confirmation email is sent to their provided email address

Benefits of guest booking:
- Reduces friction in the booking process
- Increases conversion rates for new clients
- Provides a seamless experience for first-time visitors

### Staff Creating Appointments
1. Click the "+" button in the calendar or "New Appointment" button
2. Select or search for a client (or create a new client)
3. Select service(s) to be provided
4. Choose available date and time
5. Select staff member to perform the service
6. Add any notes or special requirements
7. Assign a room if applicable
8. Set the appointment status (default is "Confirmed")
9. Click "Save Appointment"

### Quick Appointment Creation
For faster appointment booking:
1. Right-click on the desired time slot in the calendar
2. Select "Quick Book"
3. Choose client and service
4. Click "Create"

### Recurring Appointments
For regular appointments:
1. Create a new appointment
2. Check "Recurring Appointment" option
3. Set recurrence pattern:
   - Weekly, bi-weekly, monthly
   - End date or number of occurrences
4. Click "Save Recurring Series"

### Group Appointments
For services that can accommodate multiple clients:
1. Create a new appointment
2. Check "Group Appointment" option
3. Set maximum number of participants
4. Add initial client
5. Save the appointment
6. Use "Add Client to Group" to add more participants

## Managing Appointments

### Editing Appointments
1. Click on the appointment in the calendar
2. Click "Edit" button
3. Modify any appointment details
4. For recurring appointments, choose to edit:
   - Just this occurrence
   - This and all future occurrences
   - All occurrences in the series
5. Click "Save Changes"

### Rescheduling Appointments
1. Click on the appointment in the calendar
2. Click "Reschedule" button
3. Select new date and time
4. Check staff availability
5. Click "Save Changes"
6. System will automatically notify the client of the change

### Cancelling Appointments
1. Click on the appointment in the calendar
2. Click "Cancel" button
3. Select cancellation reason
4. Choose whether to apply cancellation fee (if applicable)
5. Decide whether to notify the client
6. Click "Confirm Cancellation"

### Appointment Check-in
When clients arrive:
1. Find their appointment in the calendar
2. Click "Check In" button
3. Confirm client arrival time
4. Client status changes to "Checked In"
5. Staff is notified that the client has arrived

### Completing Appointments
After service is finished:
1. Click on the appointment
2. Click "Complete" button
3. Add any service notes
4. Record products used or sold
5. Proceed to payment processing if needed
6. Click "Mark as Complete"

### No-Show Management
For clients who don't arrive:
1. Click on the appointment
2. Click "No-Show" button
3. Select whether to apply no-show fee
4. Choose whether to contact the client
5. Click "Confirm No-Show"

## Client Appointment Features

### Online Booking
Clients can book their own appointments:
1. Client logs into their account
2. Navigates to "Book Appointment"
3. Selects desired service
4. Chooses preferred staff member (optional)
5. Views available time slots
6. Selects preferred date and time
7. Confirms booking details
8. Receives confirmation email

### Viewing Upcoming Appointments
Clients can view their scheduled appointments:
1. Log into client account
2. Navigate to "My Appointments"
3. View list of upcoming appointments
4. Click on any appointment for details

### Cancelling or Rescheduling
Clients can manage their appointments within policy limits:
1. Log into client account
2. Navigate to "My Appointments"
3. Find the appointment to modify
4. Click "Reschedule" or "Cancel"
5. For rescheduling, select new available time
6. For cancellation, confirm action
7. System applies any applicable fees based on business policy

### Appointment History
Clients can view past appointments:
1. Log into client account
2. Navigate to "My Appointments"
3. Click "History" tab
4. View list of past appointments with:
   - Service details
   - Staff member
   - Date and time
   - Payment information
   - Service notes (if shared with client)

## Appointment Notifications

### Automated Reminders
The system sends automatic reminders:
- Email confirmation when appointment is booked
- SMS reminder 24-48 hours before appointment (configurable)
- Email reminder with any preparation instructions
- Follow-up message after appointment for feedback

### Configuring Notifications
Admins can configure notification settings:
1. Navigate to Settings > Notifications
2. Set timing for reminders
3. Customize message templates
4. Enable/disable different notification types
5. Set default notification preferences for new clients

## Best Practices for Appointment Management

- Leave appropriate buffer time between appointments
- Set realistic service durations to prevent scheduling conflicts
- Regularly check the next day's appointments for any issues
- Use appointment notes to record client preferences and requirements
- Confirm appointments with clients to reduce no-shows
- Use color coding to quickly identify appointment status
- Train all staff on proper appointment management procedures


## Roadmap


# Appointment Management Feature Audit Results

## Features Present in Codebase:

### Basic Appointment CRUD Operations
- Creating, reading, updating, and deleting appointments
- Appointment status management (scheduled, confirmed, completed, cancelled)
- Appointment details storage (client, staff, services, time, notes)

### Calendar Views
- Day, week, and month views implemented with FullCalendar
- Color coding for different appointment statuses

### Filtering
- Filter by staff member and status
- Calendar-based filtering and display

### Online Booking
- Client-facing booking interface
- Staff availability checking
- Booking API endpoints

### Appointment Reminders
- Email reminder templates
- Configurable reminder settings
- Jobs for sending reminders

### Status Management
- Status transitions (scheduled → confirmed → completed)
- Cancellation with reason tracking
- No-show status tracking

## Missing Features (Gaps):

### Recurring Appointments
- No implementation for creating appointment series
- Missing functionality to edit single or all occurrences
- No recurrence pattern management (weekly, bi-weekly, monthly)
- No database structure to link related recurring appointments

### Group Appointments
- No support for multiple clients in a single appointment
- Missing UI for adding clients to group appointments
- No capacity management for group services

### Quick Booking
- No streamlined quick booking UI or workflow
- Missing right-click or shortcut functionality for rapid appointment creation

### Check-in Workflow
- No explicit check-in feature or button
- Missing client arrival tracking
- No staff notification system for client arrival

### No-show Management
- Basic status tracking exists, but no dedicated workflow
- Missing no-show fee application
- No automated follow-up for no-shows

### Notification Configuration
- Basic reminder functionality exists
- Missing comprehensive notification settings UI
- Limited customization of message templates

## Implementation Recommendations

To fully implement the missing features, I recommend the following approach:

### Recurring Appointments
- Create a new `appointment_series` table to store recurrence patterns
- Add relationships between appointments and series
- Implement UI for creating and managing recurring appointments
- Develop logic for editing single vs. all occurrences

### Group Appointments
- Modify the appointment model to support multiple clients
- Create a many-to-many relationship between appointments and clients
- Implement UI for adding/removing clients from appointments
- Add capacity management for services

### Quick Booking
- Create a streamlined booking modal with minimal required fields
- Add right-click context menu to calendar
- Implement keyboard shortcuts for common booking actions

### Check-in Workflow
- Add check-in status and timestamp to appointment model
- Create check-in button and workflow in appointment views
- Implement staff notifications for client arrival

### No-show Management
- Develop dedicated no-show workflow with fee application
- Create automated follow-up options for no-shows
- Add reporting for no-show statistics

### Notification Configuration
- Expand notification settings UI
- Implement template customization
- Add more notification channels and triggers

Would you like me to prioritize these features or provide more detailed implementation plans for any specific feature?

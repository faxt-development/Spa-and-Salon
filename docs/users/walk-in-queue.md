# Walk-in Queue Management

This guide covers all aspects of managing walk-in clients in the Spa & Salon Management Software, including adding clients to the queue, managing the queue, and estimating wait times.

## Adding Walk-in Clients

### Creating a Walk-in Entry
1. Navigate to "Walk-in Queue" from the main menu
2. Click "Add Walk-in Client" button
3. Choose one of the following options:
   - Select an existing client (search by name, phone, or email)
   - Create a new client (enter basic information)
   - Add as anonymous client (minimal information required)
4. Select requested service(s)
5. Choose preferred staff member (optional)
6. Add any special notes or requirements
7. Click "Add to Queue"

### Quick Add Feature
For busy periods with multiple walk-ins:
1. Click "Quick Add" button in the Walk-in Queue
2. Enter client name or phone number
3. Select service category
4. Click "Add to Queue"
5. Complete full details when time permits

### Walk-in from Appointment Conversion
Convert a walk-in to a scheduled appointment:
1. Select the client in the walk-in queue
2. Click "Convert to Appointment"
3. Select available time slot (current day or future)
4. Complete appointment details
5. Click "Create Appointment"
6. Client is automatically removed from walk-in queue

## Managing the Queue

### Viewing the Queue
The walk-in queue displays:
- Client name
- Requested service(s)
- Estimated wait time
- Arrival time
- Preferred staff member
- Current status
- Priority level (if applicable)

### Queue Statuses
Clients in the queue can have different statuses:
- **Waiting**: Client is in the waiting area
- **Ready**: Client is next in line
- **In Service**: Client is currently receiving service
- **Completed**: Service is finished
- **Cancelled**: Client left without service
- **No-Show**: Client was called but not present

### Changing Client Status
1. Find the client in the queue
2. Click the status dropdown
3. Select the new status
4. Add notes if necessary
5. Click "Update"

### Starting Service
When ready to serve a walk-in client:
1. Find the client in the queue
2. Click "Start Service" button
3. Confirm the staff member providing the service
4. Client status changes to "In Service"
5. Timer starts to track service duration

### Completing Service
After service is finished:
1. Find the client in the queue
2. Click "Complete" button
3. Add any service notes
4. Record products used or sold
5. Proceed to payment processing
6. Client is moved to "Completed" status and removed from active queue

### Removing Clients from Queue
For clients who leave or cancel:
1. Find the client in the queue
2. Click "Remove" button
3. Select reason (cancelled, no-show, etc.)
4. Add any notes
5. Click "Confirm"

### Queue Priority Management
For clients who need priority service:
1. Find the client in the queue
2. Click "Set Priority" button
3. Select priority level (Normal, High, Urgent)
4. Add reason for priority change
5. Click "Update"
6. Client is moved up in the queue based on priority level

## Wait Time Estimation

### Automatic Wait Time Calculation
The system automatically calculates estimated wait times based on:
- Number of clients ahead in the queue
- Estimated duration of services requested by each client
- Available staff for the requested services
- Historical service time data

### Communicating Wait Times
Staff can communicate wait times to clients:
1. View the estimated wait time in the queue display
2. Inform the client of the approximate wait
3. Offer the option to receive SMS notification when their turn is approaching

### SMS Notifications
Set up automatic notifications for waiting clients:
1. Select the client in the queue
2. Click "Enable Notifications"
3. Verify client's mobile number
4. Client will receive:
   - Initial wait time estimate
   - Updates if wait time changes significantly
   - Notification when they're next in line

### Wait Time Display
For businesses with a physical waiting area display:
1. Navigate to Settings > Walk-in Queue
2. Enable "Public Queue Display"
3. Configure what information is shown (client name/initials, wait time, etc.)
4. Connect to a secondary monitor or TV for clients to view

## Queue Analytics and Reporting

### Real-time Analytics
View current queue statistics:
1. Navigate to Walk-in Queue > Analytics
2. View real-time metrics:
   - Average current wait time
   - Number of clients in queue
   - Service distribution
   - Staff utilization

### Historical Queue Reports
Generate reports on queue performance:
1. Navigate to Reports > Walk-in Queue
2. Set date range for analysis
3. View metrics such as:
   - Average wait times by day/time
   - Peak walk-in periods
   - Most requested walk-in services
   - Conversion rate from walk-in to appointment
   - Cancellation/no-show rates

## Best Practices for Walk-in Queue Management

- Regularly update queue statuses to maintain accurate wait times
- Train staff to provide realistic wait time estimates
- Use the SMS notification feature to improve client experience
- Monitor queue analytics to optimize staffing during peak walk-in times
- Consider implementing a hybrid model with some reserved slots for walk-ins
- Use priority levels sparingly and consistently
- Regularly clean up the queue by removing completed or cancelled entries
- Consider offering incentives for walk-in clients to book future appointments

## Implementation Roadmap

### Current Implementation Status
✅ **Basic Walk-in Model**
- Walk-in creation with client details
- Basic status tracking (waiting, in_service, completed, cancelled)
- Party size tracking
- Basic wait time estimation
- Service and staff assignment
- Check-in and service timing

✅ **API Endpoints**
- Basic queue statistics endpoint
- Wait time calculation

### Missing Features & Implementation Priority

#### High Priority
1. **Walk-in Queue Management UI**
   - Queue display with sortable/filterable list
   - Status update controls
   - Service start/complete flows
   - Priority management interface

2. **Client Management**
   - Client lookup/creation from walk-in form
   - Anonymous client handling
   - Client history view

3. **Service Assignment**
   - Multiple service support
   - Service duration-based wait time calculation
   - Staff availability integration

#### Medium Priority
4. **Wait Time Estimation**
   - Dynamic calculation based on:
     - Number of clients ahead
     - Service durations
     - Staff availability
   - Historical service time data

5. **Queue Status Board**
   - Public display view
   - Configurable display options
   - Kiosk mode for client self-check-in

6. **SMS Notifications**
   - Client opt-in/out
   - Wait time updates
   - Ready notifications
   - Missed opportunity alerts

#### Lower Priority
7. **Analytics & Reporting**
   - Real-time queue metrics
   - Historical reporting
   - Service demand analysis
   - Staff performance metrics

8. **Advanced Features**
   - Mobile check-in
   - Digital consent forms
   - Pre-service forms
   - Payment integration

### Technical Implementation Notes

1. **Database Changes Needed**
   - Add priority levels (normal, high, urgent)
   - Track notification preferences
   - Add fields for SMS notification tracking
   - Support for multiple services per walk-in

2. **New Controllers Required**
   - WalkInQueueController (web)
   - WalkInNotificationController (API)
   - QueueDisplayController (for public display)

3. **Frontend Components**
   - Queue management interface
   - Client lookup component
   - Service selection modal
   - Status update controls
   - Public display view

4. **Integration Points**
   - SMS notification service (Twilio, etc.)
   - Staff availability system
   - Client management system
   - Reporting dashboard

### Next Steps
1. Implement basic queue management UI
2. Add client lookup/creation to walk-in form
3. Enhance wait time calculation logic
4. Implement priority queue management
5. Add SMS notification system
6. Develop public queue display
7. Implement analytics and reporting

@extends('layouts.app-content')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">How to Schedule Appointments</h1>

                <div class="prose max-w-none">
                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Overview</h2>
                        <p class="mb-4">
                            This tutorial will guide you through the process of scheduling appointments in the Faxtina system.
                            Properly managing appointments is essential for running your salon or spa efficiently and providing
                            excellent customer service.
                        </p>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Prerequisites</h2>
                        <p class="mb-2">Before scheduling appointments, ensure you have:</p>
                        <ul class="list-disc pl-5 mb-4">
                            <li>Added staff members who will provide services</li>
                            <li>Created services that clients can book</li>
                            <li>Configured appointment settings</li>
                            <li>Set up staff availability</li>
                        </ul>
                        <p>
                            If you haven't completed these steps, please visit the
                            <a href="{{ route('admin.onboarding-checklist') }}" class="text-accent-600 hover:text-accent-800 font-medium">onboarding checklist</a>
                            to get started.
                        </p>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Step 1: Navigate to Appointments</h2>
                        <p class="mb-4">
                            Click on "Appointments" in the main navigation menu to access the appointments dashboard.
                            This page displays all upcoming and past appointments, allowing you to manage them efficiently.
                        </p>
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <p class="text-sm text-gray-600">
                                <strong>Tip:</strong> You can filter appointments by date, staff member, or status to find specific appointments quickly.
                            </p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Step 2: Create a New Appointment</h2>
                        <p class="mb-2">To schedule a new appointment:</p>
                        <ol class="list-decimal pl-5 mb-4">
                            <li class="mb-2">Click the "New Appointment" button at the top of the appointments page</li>
                            <li class="mb-2">Select an existing client or enter details for a new client</li>
                            <li class="mb-2">Choose the staff member who will provide the service</li>
                            <li class="mb-2">Select the date for the appointment</li>
                            <li class="mb-2">Choose a start time from the available slots</li>
                            <li class="mb-2">Select the service(s) the client is booking</li>
                            <li class="mb-2">Add any notes or special instructions</li>
                            <li>Click "Schedule Appointment" to confirm</li>
                        </ol>
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <p class="text-sm text-gray-600">
                                <strong>Note:</strong> The system will automatically calculate the end time based on the service duration.
                                If you've enabled sequential booking in your appointment settings, clients can book multiple services back-to-back.
                            </p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Step 3: Managing Existing Appointments</h2>
                        <p class="mb-2">You can perform several actions on existing appointments:</p>
                        <ul class="list-disc pl-5 mb-4">
                            <li class="mb-2">
                                <strong>View Details:</strong> Click on an appointment to see all details, including client information,
                                services booked, and payment status
                            </li>
                            <li class="mb-2">
                                <strong>Edit:</strong> Click the "Edit" button to modify appointment details such as time, services, or staff
                            </li>
                            <li class="mb-2">
                                <strong>Cancel:</strong> Use the "Cancel" option to cancel an appointment and provide a reason
                            </li>
                            <li class="mb-2">
                                <strong>Mark as Completed:</strong> After the appointment is finished, mark it as completed to update your records
                            </li>
                            <li>
                                <strong>Mark as No-Show:</strong> If the client doesn't arrive, mark the appointment as a no-show for tracking purposes
                            </li>
                        </ul>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Step 4: Appointment Reminders</h2>
                        <p class="mb-4">
                            If you've enabled customer reminders in your appointment settings, the system will automatically send
                            email or SMS reminders to clients before their appointments. You can configure the timing of these reminders
                            in the appointment settings.
                        </p>
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <p class="text-sm text-gray-600">
                                <strong>Best Practice:</strong> Set reminders to be sent 24-48 hours before appointments to reduce no-shows.
                            </p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Step 5: Handling Special Situations</h2>

                        <h3 class="text-lg font-medium text-gray-800 mb-2">Rescheduling</h3>
                        <p class="mb-4">
                            To reschedule an appointment, edit the existing appointment and change the date and/or time.
                            The system will check for availability and update the appointment accordingly.
                        </p>

                        <h3 class="text-lg font-medium text-gray-800 mb-2">Adding Services</h3>
                        <p class="mb-4">
                            If a client wants to add services during their visit, edit the appointment and add the additional services.
                            The system will recalculate the total price and duration.
                        </p>

                        <h3 class="text-lg font-medium text-gray-800 mb-2">Handling Conflicts</h3>
                        <p class="mb-4">
                            If you try to schedule an appointment that conflicts with another booking, the system will alert you.
                            You can then choose an alternative time or staff member.
                        </p>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Advanced Features</h2>

                        <h3 class="text-lg font-medium text-gray-800 mb-2">Waitlist Management</h3>
                        <p class="mb-4">
                            If you've enabled the waitlist feature in your appointment settings, you can add clients to a waitlist
                            for fully booked time slots. If a cancellation occurs, you can quickly fill the slot from your waitlist.
                        </p>

                        <h3 class="text-lg font-medium text-gray-800 mb-2">Time-Based Pricing</h3>
                        <p class="mb-4">
                            If you've enabled time-based pricing, you can set different prices for services based on the time of day
                            or day of the week. This is useful for implementing peak and off-peak pricing.
                        </p>

                        <h3 class="text-lg font-medium text-gray-800 mb-2">Recurring Appointments</h3>
                        <p class="mb-4">
                            For clients who book regular appointments (e.g., weekly or monthly), you can set up recurring appointments
                            to save time and ensure consistent scheduling.
                        </p>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-medium text-gray-900 mb-4">Best Practices</h2>
                        <ul class="list-disc pl-5 mb-4">
                            <li class="mb-2">
                                Regularly check your appointment schedule at the start and end of each day
                            </li>
                            <li class="mb-2">
                                Configure appropriate padding time between appointments to allow for cleanup and preparation
                            </li>
                            <li class="mb-2">
                                Use the notes field to record client preferences or special requirements
                            </li>
                            <li class="mb-2">
                                Set up staff availability accurately to prevent double-booking
                            </li>
                            <li>
                                Review no-show statistics regularly to identify patterns and implement preventive measures
                            </li>
                        </ul>
                    </div>

                    <div class="mt-10 pt-6 border-t border-gray-200">
                        <p class="text-gray-600">
                            For more assistance with appointment scheduling or other features, please contact support or refer to the
                            complete documentation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

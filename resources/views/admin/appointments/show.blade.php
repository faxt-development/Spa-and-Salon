<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Appointment Details') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('admin.appointments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Back to Appointments
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Appointment #{{ $appointment->id }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                {{ $appointment->start_time->format('F j, Y') }} • 
                                {{ $appointment->start_time->format('g:i A') }} - {{ $appointment->end_time->format('g:i A') }}
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full" 
                              :class="{
                                  'bg-green-100 text-green-800': $appointment->status === 'completed',
                                  'bg-blue-100 text-blue-800': $appointment->status === 'scheduled',
                                  'bg-yellow-100 text-yellow-800': $appointment->status === 'in_progress',
                                  'bg-red-100 text-red-800': in_array($appointment->status, ['cancelled', 'no_show']),
                                  'bg-gray-100 text-gray-800': !in_array($appointment->status, ['completed', 'scheduled', 'in_progress', 'cancelled', 'no_show'])
                              }">
                            {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                        </span>
                    </div>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Client</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $appointment->client->name }}
                                <p class="text-sm text-gray-500">
                                    {{ $appointment->client->email }}
                                    @if($appointment->client->phone)
                                        • {{ $appointment->client->formatted_phone }}
                                    @endif
                                </p>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Staff</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $appointment->staff->name }}
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Services</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($appointment->services as $service)
                                        <li>{{ $service->name }} ({{ $service->duration }} min) - ${{ number_format($service->price / 100, 2) }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $appointment->notes ?? 'No notes provided' }}
                            </dd>
                        </div>
                        @if($appointment->transactions->isNotEmpty())
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Payment</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-300">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white">
                                                @foreach($appointment->transactions as $transaction)
                                                    <tr>
                                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                                            {{ $transaction->created_at->format('M j, Y g:i A') }}
                                                        </td>
                                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                            {{ ucfirst($transaction->type) }}
                                                        </td>
                                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                {{ ucfirst($transaction->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-medium">
                                                            ${{ number_format($transaction->amount / 100, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-right pr-3 py-2 text-sm font-medium text-gray-900">Total:</td>
                                                    <td class="text-right pr-3 py-2 text-sm font-medium text-gray-900">
                                                        ${{ number_format($appointment->transactions->sum('amount') / 100, 2) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
                <div class="px-4 py-4 sm:px-6 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('admin.appointments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Back to List
                    </a>
                    <a href="{{ route('admin.appointments.edit', $appointment) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Appointment
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

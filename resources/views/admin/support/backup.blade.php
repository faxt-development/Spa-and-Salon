@extends('layouts.admin')

@section('title', 'Backup Procedures')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Backup Procedures</h1>
        
        <div class="mb-8">
            <div class="bg-blue-50 p-5 rounded-lg border border-blue-100 mb-6">
                <h2 class="text-xl font-semibold mb-3 text-blue-700">Why Backups Matter</h2>
                <p class="text-gray-700 mb-3">Regular backups are crucial for protecting your business data. They help you recover from hardware failures, accidental deletions, cyber attacks, and other data loss scenarios.</p>
                <p class="text-gray-700">Faxtina automatically backs up your data, but it's important to understand how our backup system works and what additional steps you can take to ensure your data is protected.</p>
            </div>
            
            <div class="space-y-6">
                <div class="border border-gray-200 rounded-lg p-5">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Automatic Backups</h3>
                    <p class="text-gray-600 mb-4">Faxtina performs automatic backups of your data according to the following schedule:</p>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Backup Type</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retention Period</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="py-3 px-4 text-sm text-gray-700">Incremental Backup</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">Daily</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">7 days</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 text-sm text-gray-700">Full Backup</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">Weekly</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">4 weeks</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 text-sm text-gray-700">Monthly Archive</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">Monthly</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">12 months</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-5">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Manual Data Export</h3>
                    <p class="text-gray-600 mb-4">In addition to our automatic backups, we recommend regularly exporting your critical data. This provides an additional layer of protection and gives you direct access to your data.</p>
                    
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="font-medium text-gray-800 mb-2">Client Data Export</h4>
                            <p class="text-gray-600 mb-3">Export your client database including contact information and appointment history.</p>
                            <a href="{{ route('admin.reports.clients.export') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export Client Data
                            </a>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="font-medium text-gray-800 mb-2">Sales Reports</h4>
                            <p class="text-gray-600 mb-3">Export detailed sales reports for your financial records.</p>
                            <a href="{{ route('admin.reports.sales') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                View Sales Reports
                            </a>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="font-medium text-gray-800 mb-2">Inventory Data</h4>
                            <p class="text-gray-600 mb-3">Export your current inventory levels and product information.</p>
                            <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                View Inventory
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-5">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Disaster Recovery Plan</h3>
                    <p class="text-gray-600 mb-4">In the event of data loss, follow these steps to recover your data:</p>
                    
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Contact Faxtina support immediately at <a href="mailto:support@faxtina.com" class="text-blue-600 hover:underline">support@faxtina.com</a> or call <a href="tel:+18005551234" class="text-blue-600 hover:underline">1-800-555-1234</a>.</li>
                        <li>Provide your account details and describe the data loss situation.</li>
                        <li>Our support team will help restore your data from the most recent backup.</li>
                        <li>For critical situations, we offer expedited recovery services.</li>
                    </ol>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-5">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Best Practices</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li>Regularly export important data (at least monthly).</li>
                        <li>Store exported data in a secure location separate from your main system.</li>
                        <li>Test data restoration procedures periodically to ensure they work.</li>
                        <li>Keep your emergency contact information up to date.</li>
                        <li>Train staff on basic data security practices.</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 p-5 rounded-lg border border-green-100">
            <h2 class="text-lg font-semibold mb-3 text-green-700">Need Help?</h2>
            <p class="text-gray-700 mb-4">Our support team is available to assist you with any questions about data backups or recovery procedures.</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="mailto:support@faxtina.com" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Contact Support
                </a>
                <a href="{{ route('admin.support.docs') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    View Documentation
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

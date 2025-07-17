@extends('layouts.admin')

@section('title', 'Welcome Email Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Welcome Email Management</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded">
            Back to Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Create New Welcome Email Template</h2>
        <form action="{{ route('admin.email.welcome.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
                <div>
                    <label for="from_name" class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name', config('mail.from.name')) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
                <div>
                    <label for="from_email" class="block text-sm font-medium text-gray-700 mb-1">From Email</label>
                    <input type="email" name="from_email" id="from_email" value="{{ old('from_email', config('mail.from.address')) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
            </div>
            <div class="mt-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Email Content</label>
                <textarea name="content" id="content" rows="10" required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('content') }}</textarea>
                <p class="text-sm text-gray-500 mt-1">You can use the following variables: {client_name}, {company_name}, {login_link}</p>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                    Create Template
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Existing Welcome Email Templates</h2>
        
        @if($welcomeTemplates->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($welcomeTemplates as $template)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $template->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->from_name }} ({{ $template->from_email }})</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $template->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($template->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                    <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500">No welcome email templates found. Create one using the form above.</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Initialize a rich text editor for the email content
    document.addEventListener('DOMContentLoaded', function() {
        // This is a placeholder for a rich text editor initialization
        // You would typically use something like TinyMCE, CKEditor, or Quill
        console.log('Email editor initialized');
    });
</script>
@endpush
@endsection

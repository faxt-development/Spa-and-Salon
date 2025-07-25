@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edit Drip Campaign</h2>

                <form action="{{ route('drip-campaigns.update', $campaign) }}" method="POST" enctype="multipart/form-data"
                      x-data="{
                        form: {
                            name: '{{ old('name', $campaign->name) }}',
                            subject: '{{ old('subject', $campaign->subject) }}',
                            preview_text: '{{ old('preview_text', $campaign->preview_text) }}',
                            segment: '{{ old('segment', $campaign->segment) }}',
                            scheduled_for: '{{ old('scheduled_for', optional($campaign->scheduled_for)->format('Y-m-d\TH:i')) }}',
                            content: `{!! old('content', $campaign->content) !!}`
                        },
                        showPreview: false,
                        previewRecipient: {
                            first_name: 'John',
                            last_name: 'Doe',
                            email: 'john@example.com'
                        },

                        init() {
                            // Initialize any listeners or setup
                        },

                        updatePreview() {
                            this.showPreview = true;
                        },

                        insertMergeTag(tag) {
                            const textarea = this.$refs.editor;
                            const start = textarea.selectionStart;
                            const end = textarea.selectionEnd;
                            const text = textarea.value;
                            const before = text.substring(0, start);
                            const after = text.substring(end, text.length);

                            this.form.content = before + tag + after;

                            // Set cursor position after the inserted tag
                            this.$nextTick(() => {
                                const newCursorPos = start + tag.length;
                                textarea.setSelectionRange(newCursorPos, newCursorPos);
                                textarea.focus();
                            });
                        }
                      }"
                      @submit="$event.preventDefault(); $refs.form.submit()">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Campaign Info -->
                        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Campaign Details</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Campaign Name *</label>
                                    <input type="text" id="name" name="name" x-model="form.name" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="segment" class="block text-sm font-medium text-gray-700">Recipient Segment *</label>
                                    <select id="segment" name="segment" x-model="form.segment" required
                                            class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                                            :disabled="{{ $campaign->isSent() ? 'true' : 'false' }}">
                                        <option value="all_clients">All Clients</option>
                                        <option value="active_clients">Active Clients (past 60 days)</option>
                                        <option value="inactive_clients">Inactive Clients (60+ days)</option>
                                        <option value="high_value">High-Value Clients</option>
                                        <option value="new_clients">New Clients (past 30 days)</option>
                                        <option value="birthday_this_month">Birthdays This Month</option>
                                        <option value="anniversary_this_month">Anniversaries This Month</option>
                                    </select>
                                    @error('segment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if(!$campaign->isSent())
                                <div>
                                    <label for="scheduled_for" class="block text-sm font-medium text-gray-700">Schedule Send</label>
                                    <input type="datetime-local" id="scheduled_for" name="scheduled_for"
                                           x-model="form.scheduled_for"
                                           min="{{ now()->format('Y-m-d\TH:i') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Leave blank to save as draft</p>
                                    @error('scheduled_for')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Email Content -->
                        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Email Content</h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject *</label>
                                    <input type="text" id="subject" name="subject" x-model="form.subject" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('subject')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="preview_text" class="block text-sm font-medium text-gray-700">Preview Text</label>
                                    <input type="text" id="preview_text" name="preview_text" x-model="form.preview_text"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                           placeholder="A preview of what's inside...">
                                    <p class="mt-1 text-xs text-gray-500">Appears in the inbox after the subject line</p>
                                    @error('preview_text')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <div class="flex justify-between items-center">
                                        <label for="content" class="block text-sm font-medium text-gray-700">Email Content *</label>
                                        <div class="text-sm">
                                            <button type="button" @click="showPreview = !showPreview" class="text-blue-600 hover:text-blue-800">
                                                <span x-text="showPreview ? 'Hide Preview' : 'Show Preview'"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Merge Tags -->
                                    <div class="mt-1 mb-2 flex flex-wrap gap-1">
                                        <span class="text-xs text-gray-500">Insert merge tags:</span>
                                        <template x-for="tag in ['{first_name}', '{last_name}', '{email}']" :key="tag">
                                            <button type="button" @click="insertMergeTag(tag)"
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                                                <span x-text="tag"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Editor/Preview Toggle -->
                                    <div x-show="!showPreview">
                                        <textarea id="content" name="content" x-model="form.content" rows="15" required
                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                                  placeholder="Write your email content here..."></textarea>
                                        @error('content')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Email Preview -->
                                    <div x-show="showPreview" class="mt-2 p-4 border border-gray-200 rounded-md bg-white">
                                        <div class="border-b border-gray-200 pb-2 mb-4">
                                            <div class="font-semibold" x-text="form.subject || '(No subject)'"></div>
                                            <div class="text-xs text-gray-500" x-text="form.preview_text || 'A preview of what\'s inside...'"></div>
                                        </div>
                                        <div class="prose max-w-none" x-html="form.content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campaign Stats (if sent) -->
                        @if($campaign->isSent())
                        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Campaign Statistics</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-3xl font-bold text-gray-900">{{ $campaign->sent_count }}</div>
                                    <div class="text-sm text-gray-500">Sent</div>
                                </div>
                                <div class="bg-primary-50 p-4 rounded-lg text-center">
                                    <div class="text-3xl font-bold text-blue-900">{{ $campaign->opened_count }}</div>
                                    <div class="text-sm text-blue-600">Opened ({{ $campaign->open_rate }}%)</div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg text-center">
                                    <div class="text-3xl font-bold text-green-900">{{ $campaign->clicked_count }}</div>
                                    <div class="text-sm text-green-600">Clicked ({{ $campaign->click_rate }}%)</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Form Actions -->
                        <div class="flex justify-between items-center">
                            <div>
                                @if(!$campaign->isSent())
                                <button type="button"
                                        @click="if(confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Delete Campaign
                                </button>
                                @endif
                            </div>

                            <div class="flex space-x-3">
                                <a href="{{ route('admin.drip-campaigns.index') }}"
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </a>

                                @if(!$campaign->isSent())
                                    <button type="submit" name="action" value="save_draft"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                        Save as Draft
                                    </button>

                                    <button type="submit" name="action" value="schedule"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            x-bind:disabled="!form.scheduled_for"
                                            x-bind:class="{'opacity-50 cursor-not-allowed': !form.scheduled_for}">
                                        Schedule for Later
                                    </button>

                                    <button type="submit" name="action" value="send"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Send Now
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>

                @if(!$campaign->isSent())
                <form id="delete-form" action="{{ route('drip-campaigns.destroy', $campaign) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Any additional Alpine.js initialization
    });
</script>
@endpush
@endsection

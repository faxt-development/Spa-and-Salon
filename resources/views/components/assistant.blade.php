<div x-data="assistantData" class="fixed bottom-6 right-6 z-50">
    <!-- Trigger -->
    <button @click="open = true" class="bg-accent-500 hover:bg-accent-600 text-white px-4 py-2 rounded-full shadow-lg flex items-center space-x-2 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
        <span>Ask Assistant</span>
    </button>

    <!-- Assistant Panel -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="bg-white w-96 max-h-[80vh] overflow-y-auto p-4 shadow-xl rounded-lg absolute bottom-16 right-0 border">
        
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Need help?</h2>
            <button @click="open = false" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitQuery" class="mb-4">
            <div class="relative">
                <input 
                    type="text" 
                    x-model="query" 
                    placeholder="Ask a question about Faxtina..." 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                <button 
                    type="submit" 
                    class="absolute inset-y-0 right-0 px-3 flex items-center"
                    :disabled="loading || query.trim() === ''"
                >
                    <template x-if="!loading">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 hover:text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template x-if="loading">
                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                </button>
            </div>
        </form>

        <!-- Loading state -->
        <div x-show="loading" class="flex justify-center items-center py-4">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Results -->
        <div x-show="results.length > 0 && !loading" class="space-y-4">
            <template x-for="(item, index) in results" :key="index">
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <div x-html="item.content.replace(/\n/g, '<br>')" class="text-sm"></div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                        </svg>
                        <span x-text="item.metadata && item.metadata.source ? item.metadata.source : 'Documentation'"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- No results state -->
        <div x-show="results.length === 0 && query !== '' && !loading" class="py-4 text-center text-gray-500">
            No matching results found. Try rephrasing your question.
        </div>

        <!-- Initial state -->
        <div x-show="results.length === 0 && query === '' && !loading" class="py-4 text-center text-gray-500">
            <p>Ask any question about Faxtina features, settings, or how to use the system.</p>
            <div class="mt-3 space-y-2">
                <button @click="query = 'How do I create a new appointment?'; submitQuery()" class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full text-gray-700">How do I create a new appointment?</button>
                <button @click="query = 'How to add a new staff member?'; submitQuery()" class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full text-gray-700">How to add a new staff member?</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('assistantData', () => ({
            open: false,
            query: '',
            results: [],
            loading: false,

            submitQuery() {
                if (this.query.trim() === '' || this.loading) return;
                
                this.loading = true;
                this.results = [];
                
                fetch('/assistant/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ query: this.query })
                })
                .then(res => res.json())
                .then(data => {
                    this.results = data;
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error fetching assistant results:', error);
                    this.loading = false;
                });
            }
        }));
    });
</script>

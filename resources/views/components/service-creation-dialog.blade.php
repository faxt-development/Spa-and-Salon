<div x-data="{ 
    open: false, 
    serviceName: '', 
    duration: 0, 
    price: 0,
    submit() {
        if (!this.serviceName || !this.duration || !this.price) {
            alert('Please fill out all fields');
            return;
        }
        const newService = {
            id: Date.now(), // Generate a temporary ID
            name: this.serviceName,
            description: 'Custom service',
            duration: parseInt(this.duration),
            category: 'Custom',
            price: parseFloat(this.price),
            popular: false
        };
        $dispatch('service-created', newService);
        this.open = false;
        this.serviceName = '';
        this.duration = 0;
        this.price = 0;
    }
}" x-on:open-service-dialog.window="open = true" x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md" x-show="open" x-transition>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Create New Service</h2>
            <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Service Name</label>
                <input type="text" x-model="serviceName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                <input type="number" x-model="duration" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Price</label>
                <input type="number" x-model="price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" />
            </div>
            <div class="flex justify-end space-x-2">
                <button @click="open = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">
                    Create Service
                </button>
            </div>
        </form>
    </div>
</div>

/**
 * Guest to Client Registration Handler
 * Handles the registration process from guest appointment page
 */

class GuestRegistration {
    constructor() {
        this.guestToken = this.getQueryParam('guest_token');
        this.init();
    }

    init() {
        if (this.guestToken) {
            this.setupRegistrationForm();
            this.setupEventListeners();
        }
    }

    getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    setupRegistrationForm() {
        // Pre-fill email if coming from guest appointment
        const emailField = document.querySelector('input[name="email"]');
        if (emailField && this.guestToken) {
            // We'll fetch the email from the guest token
            this.fetchGuestEmail();
        }
    }

    async fetchGuestEmail() {
        try {
            const response = await fetch(`/api/guest-appointment/${this.guestToken}/email`);
            if (response.ok) {
                const data = await response.json();
                const emailField = document.querySelector('input[name="email"]');
                if (emailField && data.email) {
                    emailField.value = data.email;
                    emailField.readOnly = true;
                }
            }
        } catch (error) {
            console.error('Failed to fetch guest email:', error);
        }
    }

    setupEventListeners() {
        // Handle registration form submission
        const registrationForm = document.querySelector('#client-registration-form');
        if (registrationForm) {
            registrationForm.addEventListener('submit', this.handleRegistration.bind(this));
        }
    }

    async handleRegistration(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Add guest token if available
        if (this.guestToken) {
            data.guest_token = this.guestToken;
        }

        try {
            const response = await fetch('/api/clients/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                this.handleRegistrationSuccess(result);
            } else {
                this.handleRegistrationError(result);
            }
        } catch (error) {
            console.error('Registration error:', error);
            this.showError('An error occurred during registration. Please try again.');
        }
    }

    handleRegistrationSuccess(result) {
        // Show success message
        this.showSuccess('Registration successful! Your appointment has been linked to your new account.');
        
        // Redirect to dashboard or appointment page after a short delay
        setTimeout(() => {
            if (this.guestToken) {
                window.location.href = `/guest-appointment/${this.guestToken}?registered=true`;
            } else {
                window.location.href = '/dashboard';
            }
        }, 2000);
    }

    handleRegistrationError(result) {
        if (result.errors) {
            this.displayValidationErrors(result.errors);
        } else {
            this.showError(result.message || 'Registration failed. Please try again.');
        }
    }

    displayValidationErrors(errors) {
        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-red-600 text-sm mt-1';
                errorDiv.textContent = errors[field][0];
                input.parentNode.appendChild(errorDiv);
            }
        });
    }

    showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
        alert.textContent = message;
        document.body.appendChild(alert);
        
        setTimeout(() => alert.remove(), 5000);
    }

    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
        alert.textContent = message;
        document.body.appendChild(alert);
        
        setTimeout(() => alert.remove(), 5000);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    new GuestRegistration();
});

// Export for use in other files
window.GuestRegistration = GuestRegistration;

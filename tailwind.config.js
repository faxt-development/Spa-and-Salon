import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    // important: false, // Removed to avoid conflicts with @apply directives
    // Use the 'important' utility class when needed instead
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
        './resources/css/**/*.css',
    ],
theme: {
  	extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
  		colors: {

            // Theme colors using CSS variables for dynamic theming
            primary: {
                DEFAULT: 'var(--primary-color, #8B9259)',
                // Generated color scale based on #8B9259 (olive green)
                50: 'var(--primary-color-50, #f6f7f2)',
                100: 'var(--primary-color-100, #e9ecdf)',
                200: 'var(--primary-color-200, #d4d9c0)',
                300: 'var(--primary-color-300, #b8bf96)',
                400: 'var(--primary-color-400, #8B9259)', // Base color
                500: 'var(--primary-color-500, #7a814e)',
                600: 'var(--primary-color-600, #636a3f)',
                700: 'var(--primary-color-700, #4d5332)',
                800: 'var(--primary-color-800, #3e4329)',
                900: 'var(--primary-color-900, #2a2e1c)',
                950: 'var(--primary-color-950, #1a1c10)',
            },
            secondary: {
                DEFAULT: 'var(--secondary-color, #EDDFC0)',
                // Generated color scale with #EDDFC0 as 500
                50: 'var(--secondary-color-50, #faf8f2)',
                100: 'var(--secondary-color-100, #f5f0e0)',
                200: 'var(--secondary-color-200, #efe6d0)',
                300: 'var(--secondary-color-300, #e8d9b8)',
                400: 'var(--secondary-color-400, #e3d2ac)',
                500: 'var(--secondary-color-500, #EDDFC0)', // Base color
                600: 'var(--secondary-color-600, #d4c5a0)',
                700: 'var(--secondary-color-700, #b8a77d)',
                800: 'var(--secondary-color-800, #8a7e5f)',
                900: 'var(--secondary-color-900, #5c5440)',
                950: 'var(--secondary-color-950, #2e2a20)'
            },
            border: {
                DEFAULT: 'var(--border, #e5e7eb)' // Default border color
            },
            ring: {
                DEFAULT: 'var(--ring, #3b82f6)' // Default ring color
            },
            accent: {
                DEFAULT: 'var(--accent-color, #F4C96C)',
                foreground: 'var(--text-color, #1F4B48)',
                // Generated color scale with #F4C96C as 500
                50: 'var(--accent-color-50, #fffbf2)',
                100: 'var(--accent-color-100, #fef7e5)',
                200: 'var(--accent-color-200, #fdf0cc)',
                300: 'var(--accent-color-300, #fbe8b3)',
                400: 'var(--accent-color-400, #f8d98a)',
                500: 'var(--accent-color-500, #F4C96C)', // Base color
                600: 'var(--accent-color-600, #e8b53d)',
                700: 'var(--accent-color-700, #c79824)',
                800: 'var(--accent-color-800, #8a6a19)',
                900: 'var(--accent-color-900, #5c460f)',
                950: 'var(--accent-color-950, #3d2f0a)',
            },
            text: {
                DEFAULT: 'var(--text-color, #1F4B48)',
                light: 'var(--text-color-light, #64748b)',
                lighter: 'var(--text-color-lighter, #94a3b8)',
            },
            background: {
                DEFAULT: 'var(--background-color, #EDDFC0)',
                secondary: 'var(--background-secondary, var(--secondary-color, #EDDFC0))'
            },
            foreground: 'var(--text-color, #1F4B48)',
            card: {
                DEFAULT: 'var(--card-bg, #EDDFC0)',
                foreground: 'var(--text-color, #1F4B48)'
            },
            popover: {
                DEFAULT: 'var(--popover-bg, #EDDFC0)',
                foreground: 'var(--text-color, #1F4B48)'
            },
            sidebar: {
  				DEFAULT: 'hsl(var(--sidebar-background))',
  				foreground: 'hsl(var(--sidebar-foreground))',
  				primary: 'hsl(var(--sidebar-primary))',
  				'primary-foreground': 'hsl(var(--sidebar-primary-foreground))',
  				accent: 'hsl(var(--sidebar-accent))',
  				'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
  				border: 'hsl(var(--sidebar-border))',
  				ring: 'hsl(var(--sidebar-ring))'
  			}
  		},
  		borderRadius: {
  			lg: 'var(--radius)',
  			md: 'calc(var(--radius) - 2px)',
  			sm: 'calc(var(--radius) - 4px)'
  		},
  		keyframes: {
  			'accordion-down': {
  				from: {
  					height: '0'
  				},
  				to: {
  					height: 'var(--radix-accordion-content-height)'
  				}
  			},
  			'accordion-up': {
  				from: {
  					height: 'var(--radix-accordion-content-height)'
  				},
  				to: {
  					height: '0'
  				}
  			}
  		},
  		animation: {
  			'accordion-down': 'accordion-down 0.2s ease-out',
  			'accordion-up': 'accordion-up 0.2s ease-out'
  		}
  	}
    },
    plugins: [forms]
};

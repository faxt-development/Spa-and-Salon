import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    important: true, // Add !important to all Tailwind utilities to ensure they override Bootstrap
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

            primary: {
                50: 'oklch(100% 0 none)',
                100: 'oklch(100% 0 none)',
                200: 'oklch(96.976% 0.00615 137.77)',
                300: 'oklch(91.4% 0.01729 128.67)',
                400: 'oklch(86.059% 0.0288 126.86)',
                500: 'oklch(80.594% 0.03831 122.27)',
                600: 'oklch(69.643% 0.05974 118.85)',
                700: 'oklch(55.828% 0.05561 114.32)',
                800: 'oklch(33.877% 0.03223 18.881)',
                900: 'oklch(19.913% 0.01162 99.509)',
                950: 'oklch(0% 0 none)',
                DEFAULT: 'oklch(80.594% 0.03831 122.27)', // optional fallback (same as 500)
              },
            secondary: {
                50: 'oklch(66.441% 0.06811 262.28)',
                100: 'oklch(63.743% 0.07163 257.58)',
                200: 'oklch(58.822% 0.07977 247.62)',
                300: 'oklch(53.709% 0.06421 235.08)',
                400: 'oklch(48.123% 0.05275 220.93)',
                500: 'oklch(41.294% 0.04439 208.63)',
                600: 'oklch(29.746% 0.03108 198.94)',
                700: 'oklch(15.239% 0.00997 196.15)',
                800: 'oklch(0% 0 none)',
                900: 'oklch(0% 0 none)',
                950: 'oklch(0% 0 none)',
                DEFAULT: 'oklch(41.294% 0.04439 208.63)', // optional fallback (same as 500)
            },
            accent: {
                DEFAULT: 'hsl(var(--accent))',
                foreground: 'hsl(var(--accent-foreground))',
                50: 'oklch(66.015% 0.12233 56.774)',
                100: 'oklch(91.498% 0.02908 60.748)',
                200: 'oklch(88.534% 0.03882 60.95)',
                300: 'oklch(82.802% 0.06042 59.377)',
                400: 'oklch(77.031% 0.08161 58.741)',
                500: 'oklch(71.54% 0.10244 58.704)',
                600: 'oklch(66.015% 0.12233 56.774)',
                700: 'oklch(56.031% 0.10835 56.186)',
                800: 'oklch(44.998% 0.08444 57.259)',
                900: 'oklch(33.258% 0.05802 58.603)',
                950: 'oklch(20.289% 0.02955 55.819)',
            },

  			background: 'hsl(var(--background))',
  			foreground: 'hsl(var(--foreground))',
  			card: {
  				DEFAULT: 'hsl(var(--card))',
  				foreground: 'hsl(var(--card-foreground))'
  			},
  			popover: {
  				DEFAULT: 'hsl(var(--popover))',
  				foreground: 'hsl(var(--popover-foreground))'
  			},
  			primary: {
  				DEFAULT: 'hsl(var(--primary))',
  				foreground: 'hsl(var(--primary-foreground))'
  			},
  			secondary: {
  				DEFAULT: 'hsl(var(--secondary))',
  				foreground: 'hsl(var(--secondary-foreground))'
  			},
  			muted: {
  				DEFAULT: 'hsl(var(--muted))',
  				foreground: 'hsl(var(--muted-foreground))'
  			},
  			destructive: {
  				DEFAULT: 'hsl(var(--destructive))',
  				foreground: 'hsl(var(--destructive-foreground))'
  			},
  			border: 'hsl(var(--border))',
  			input: 'hsl(var(--input))',
  			ring: 'hsl(var(--ring))',
  			chart: {
  				'1': 'hsl(var(--chart-1))',
  				'2': 'hsl(var(--chart-2))',
  				'3': 'hsl(var(--chart-3))',
  				'4': 'hsl(var(--chart-4))',
  				'5': 'hsl(var(--chart-5))'
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
    plugins: [forms],
};

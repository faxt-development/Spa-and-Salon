import './bootstrap';
import Alpine from 'alpinejs';

// Import dashboard initializer
import './dashboard-initializer.jsx';

// Import Tippy.js for tooltips
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

// Make libraries available globally
window.Alpine = Alpine;
window.tippy = tippy;

Alpine.start();

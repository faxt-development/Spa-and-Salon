import React from 'react';
import { createRoot } from 'react-dom/client';
import ConfigurableDashboard from './components/Dashboard/ConfigurableDashboard.jsx';

/**
 * Initialize the configurable dashboard area
 * @param {string} containerId - The ID of the container element
 * @param {Object} options - Additional options for initialization
 */
window.initConfigurableDashboard = function(containerId, options = {}) {
    const container = document.getElementById(containerId);
    
    if (!container) {
        console.error(`Container with ID "${containerId}" not found`);
        return;
    }
    
    // Get user type from the URL or data attribute
    const isAdmin = window.location.pathname.includes('/admin/') || 
                    container.dataset.userType === 'admin';
    
    const dashboardOptions = {
        ...options,
        userType: isAdmin ? 'admin' : 'client',
        compact: true // Use compact mode when embedded in existing dashboards
    };
    
    const root = createRoot(container);
    root.render(
        <React.StrictMode>
            <ConfigurableDashboard {...dashboardOptions} />
        </React.StrictMode>
    );
};

// Auto-initialize if the container exists
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('configurableDashboardArea');
    if (container) {
        window.initConfigurableDashboard('configurableDashboardArea');
    }
});

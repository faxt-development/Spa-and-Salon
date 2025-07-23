import React from 'react';
import { createRoot } from 'react-dom/client';
import { CssBaseline } from '@mui/material';
import { AppThemeProvider } from './theme/ThemeProvider';
import ConfigurableDashboard from './components/Dashboard/ConfigurableDashboard.jsx';

// Store root instances to prevent multiple initializations
const rootInstances = new Map();

/**
 * Initialize the configurable dashboard area
 * @param {string} containerId - The ID of the container element
 * @param {Object} options - Additional options for initialization
 */
window.initConfigurableDashboard = function(containerId, options = {}) {
    const container = document.getElementById(containerId);
    
    if (!container) {
        console.error(`Container with ID "${containerId}" not found`);
        return null;
    }

    // Check if root already exists for this container
    if (rootInstances.has(containerId)) {
        console.warn(`Dashboard already initialized for container "${containerId}". Using existing instance.`);
        return rootInstances.get(containerId);
    }
    
    try {
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
                <AppThemeProvider>
                    <CssBaseline />
                    <ConfigurableDashboard {...dashboardOptions} />
                </AppThemeProvider>
            </React.StrictMode>
        );

        // Store the root instance
        rootInstances.set(containerId, root);
        return root;
    } catch (error) {
        console.error('Failed to initialize dashboard:', error);
        return null;
    }
};

// Cleanup function to unmount and remove root instance
window.cleanupDashboard = function(containerId) {
    if (rootInstances.has(containerId)) {
        const root = rootInstances.get(containerId);
        root.unmount();
        rootInstances.delete(containerId);
    }
};

// Auto-initialize if the container exists
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('configurableDashboardArea');
    if (container && !rootInstances.has('configurableDashboardArea')) {
        window.initConfigurableDashboard('configurableDashboardArea');
    }
});

// Cleanup on page unload to prevent memory leaks
window.addEventListener('beforeunload', () => {
    rootInstances.forEach((_, containerId) => {
        window.cleanupDashboard(containerId);
    });
});

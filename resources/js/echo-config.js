/**
 * Laravel Echo Configuration
 * 
 * Sets up real-time broadcasting for the staff dashboard
 * Uses WebSocket/Pusher for live updates
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

/**
 * Initialize Laravel Echo with fallback support
 */
const initializeEcho = () => {
    // Check if Echo is already initialized
    if (window.Echo) {
        console.log('[Echo] Already initialized');
        return;
    }

    const broadcasterType = document.querySelector('meta[name="broadcast-driver"]')?.content || 'log';
    
    console.log('[Echo] Initializing with broadcaster:', broadcasterType);

    try {
        if (broadcasterType === 'pusher') {
            // Pusher configuration
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: document.querySelector('meta[name="pusher-app-key"]')?.content || 'demo',
                cluster: document.querySelector('meta[name="pusher-app-cluster"]')?.content || 'mt1',
                encrypted: true,
                forceTLS: true,
                wsHost: window.location.hostname,
                wsPort: 6001,
                wssPort: 6001,
                enabledTransports: ['ws', 'wss'],
                logToConsole: true
            });
            console.log('[Echo] Pusher broadcaster configured');
        } else if (broadcasterType === 'ably') {
            // Ably configuration
            window.Echo = new Echo({
                broadcaster: 'ably',
                key: document.querySelector('meta[name="ably-key"]')?.content
            });
            console.log('[Echo] Ably broadcaster configured');
        } else {
            // Log broadcaster (development fallback)
            window.Echo = new Echo({
                broadcaster: 'log'
            });
            console.log('[Echo] Log broadcaster (development mode)');
        }

        // Log successful initialization
        window.Echo.connector?.socket?.on('connect', () => {
            console.log('[Echo] WebSocket connected');
            // Dispatch custom event for app components
            window.dispatchEvent(new CustomEvent('echo:connected'));
        });

        window.Echo.connector?.socket?.on('disconnect', () => {
            console.warn('[Echo] WebSocket disconnected');
            window.dispatchEvent(new CustomEvent('echo:disconnected'));
        });

    } catch (error) {
        console.error('[Echo] Initialization error:', error);
        console.log('[Echo] Falling back to polling mode');
        // Dashboard will handle fallback to polling
    }
};

/**
 * Set up Echo error handling
 */
const setupErrorHandling = () => {
    if (!window.Echo) return;

    window.Echo.error((error) => {
        console.error('[Echo] Error:', error);
    });
};

/**
 * Helper to listen on a clinic channel
 */
window.listenToDashboard = (clinicLocation = 'Main') => {
    if (!window.Echo) {
        console.warn('[Echo] Echo not available');
        return null;
    }

    const channelName = `staff.dashboard.${clinicLocation}`;
    console.log(`[Echo] Listening to channel: ${channelName}`);

    return window.Echo.channel(channelName);
};

/**
 * Helper to listen on private channels
 */
window.listenToPrivate = (channel) => {
    if (!window.Echo) {
        console.warn('[Echo] Echo not available');
        return null;
    }

    console.log(`[Echo] Listening to private channel: ${channel}`);
    return window.Echo.private(channel);
};

/**
 * Helper to listen on presence channels
 */
window.listenToPresence = (channel) => {
    if (!window.Echo) {
        console.warn('[Echo] Echo not available');
        return null;
    }

    console.log(`[Echo] Listening to presence channel: ${channel}`);
    return window.Echo.join(channel);
};

/**
 * Initialize on document ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeEcho();
        setupErrorHandling();
    });
} else {
    initializeEcho();
    setupErrorHandling();
}

// Export for use in other modules
export {
    initializeEcho,
    setupErrorHandling
};

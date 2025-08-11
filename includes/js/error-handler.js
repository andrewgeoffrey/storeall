/**
 * Error Handler for StoreAll.io
 * Captures client-side errors and sends them to the server
 */

class ErrorHandler {
    constructor() {
        this.init();
    }
    
    init() {
        // Capture JavaScript errors
        window.addEventListener('error', (event) => {
            this.logError({
                message: event.message,
                file: event.filename,
                line: event.lineno,
                column: event.colno,
                stack: event.error ? event.error.stack : '',
                type: 'JAVASCRIPT_ERROR'
            });
        });
        
        // Capture unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.logError({
                message: event.reason?.message || 'Unhandled Promise Rejection',
                file: event.filename || '',
                line: event.lineno || 0,
                column: event.colno || 0,
                stack: event.reason?.stack || '',
                type: 'PROMISE_REJECTION'
            });
        });
        
        // Capture AJAX errors
        this.interceptAjaxErrors();
        
        // Capture fetch errors
        this.interceptFetchErrors();
    }
    
    logError(errorData) {
        // Add additional context
        const enhancedError = {
            ...errorData,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString(),
            sessionId: this.getSessionId()
        };
        
        // Send to server
        this.sendToServer(enhancedError);
        
        // Log to console in development
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.error('Client Error:', enhancedError);
        }
    }
    
    sendToServer(errorData) {
        fetch('/api/log-error', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(errorData)
        }).catch(err => {
            // Fallback: log to console if server request fails
            console.error('Failed to send error to server:', err);
        });
    }
    
    interceptAjaxErrors() {
        // Intercept XMLHttpRequest errors
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;
        
        XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
            this._method = method;
            this._url = url;
            return originalXHROpen.call(this, method, url, async, user, password);
        };
        
        XMLHttpRequest.prototype.send = function(data) {
            this.addEventListener('error', (event) => {
                window.errorHandler.logError({
                    message: 'AJAX Request Failed',
                    file: 'XMLHttpRequest',
                    line: 0,
                    column: 0,
                    stack: '',
                    type: 'AJAX_ERROR',
                    details: {
                        method: this._method,
                        url: this._url,
                        status: this.status,
                        statusText: this.statusText
                    }
                });
            });
            
            return originalXHRSend.call(this, data);
        };
    }
    
    interceptFetchErrors() {
        const originalFetch = window.fetch;
        
        window.fetch = function(...args) {
            return originalFetch.apply(this, args).catch(error => {
                window.errorHandler.logError({
                    message: 'Fetch Request Failed',
                    file: 'fetch',
                    line: 0,
                    column: 0,
                    stack: error.stack || '',
                    type: 'FETCH_ERROR',
                    details: {
                        url: args[0],
                        options: args[1]
                    }
                });
                throw error;
            });
        };
    }
    
    getSessionId() {
        // Try to get session ID from cookie or localStorage
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'PHPSESSID') {
                return value;
            }
        }
        return null;
    }
}

// Initialize error handler when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.errorHandler = new ErrorHandler();
});

// Also initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.errorHandler = new ErrorHandler();
    });
} else {
    window.errorHandler = new ErrorHandler();
}

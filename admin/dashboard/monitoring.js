/**
 * StoreAll.io - Client-Side Monitoring System
 * Collects client-side errors, performance metrics, and sends to monitoring API
 */

class MonitoringSystem {
    constructor() {
        this.apiUrl = '/admin/dashboard/monitoring_api.php';
        this.errorQueue = [];
        this.performanceQueue = [];
        this.maxQueueSize = 100;
        this.flushInterval = 30000; // 30 seconds
        this.pageLoadStart = performance.now();
        
        this.init();
    }
    
    init() {
        // Set up error monitoring
        this.setupErrorMonitoring();
        
        // Set up performance monitoring
        this.setupPerformanceMonitoring();
        
        // Set up periodic flushing
        this.setupPeriodicFlushing();
        
        // Monitor page load performance
        this.monitorPageLoad();
        
        // Monitor user interactions
        this.monitorUserInteractions();
        
        // Monitor AJAX requests
        this.monitorAjaxRequests();
    }
    
    /**
     * Set up error monitoring for JavaScript errors
     */
    setupErrorMonitoring() {
        // Global error handler
        window.addEventListener('error', (event) => {
            this.logClientError({
                error_message: event.message,
                page_url: window.location.href,
                line_number: event.lineno,
                column_number: event.colno,
                error_name: event.error?.name || 'Error',
                stack_trace: event.error?.stack || null,
                browser_info: this.getBrowserInfo(),
                page_load_time: this.getPageLoadTime()
            });
        });
        
        // Promise rejection handler
        window.addEventListener('unhandledrejection', (event) => {
            this.logClientError({
                error_message: event.reason?.message || 'Unhandled Promise Rejection',
                page_url: window.location.href,
                line_number: 0,
                error_name: 'PromiseRejection',
                stack_trace: event.reason?.stack || null,
                browser_info: this.getBrowserInfo(),
                page_load_time: this.getPageLoadTime()
            });
        });
        
        // Console error interceptor
        this.interceptConsoleErrors();
    }
    
    /**
     * Intercept console errors to capture them
     */
    interceptConsoleErrors() {
        const originalError = console.error;
        const originalWarn = console.warn;
        
        console.error = (...args) => {
            this.logClientError({
                error_message: args.join(' '),
                page_url: window.location.href,
                line_number: this.getCallerLine(),
                error_name: 'ConsoleError',
                browser_info: this.getBrowserInfo(),
                page_load_time: this.getPageLoadTime()
            });
            originalError.apply(console, args);
        };
        
        console.warn = (...args) => {
            this.logClientError({
                error_message: args.join(' '),
                page_url: window.location.href,
                line_number: this.getCallerLine(),
                error_name: 'ConsoleWarning',
                level: 'WARNING',
                browser_info: this.getBrowserInfo(),
                page_load_time: this.getPageLoadTime()
            });
            originalWarn.apply(console, args);
        };
    }
    
    /**
     * Set up performance monitoring
     */
    setupPerformanceMonitoring() {
        // Monitor page load performance
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.entryType === 'navigation') {
                            this.logPerformanceMetric({
                                metric_type: 'page_load',
                                value: entry.loadEventEnd - entry.loadEventStart,
                                unit: 'ms',
                                page_url: window.location.href,
                                additional_data: {
                                    dom_content_loaded: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart,
                                    first_paint: entry.firstPaint || 0,
                                    first_contentful_paint: entry.firstContentfulPaint || 0
                                }
                            });
                        }
                    }
                });
                observer.observe({ entryTypes: ['navigation'] });
            } catch (e) {
                console.warn('PerformanceObserver not supported:', e);
            }
        }
        
        // Monitor resource loading
        this.monitorResourceLoading();
    }
    
    /**
     * Monitor resource loading performance
     */
    monitorResourceLoading() {
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.entryType === 'resource') {
                            this.logPerformanceMetric({
                                metric_type: 'resource_load',
                                value: entry.duration,
                                unit: 'ms',
                                page_url: window.location.href,
                                additional_data: {
                                    resource_name: entry.name,
                                    resource_type: entry.initiatorType,
                                    transfer_size: entry.transferSize,
                                    decoded_body_size: entry.decodedBodySize
                                }
                            });
                        }
                    }
                });
                observer.observe({ entryTypes: ['resource'] });
            } catch (e) {
                console.warn('Resource monitoring not supported:', e);
            }
        }
    }
    
    /**
     * Monitor page load performance
     */
    monitorPageLoad() {
        // Use traditional timing if PerformanceObserver is not available
        window.addEventListener('load', () => {
            const loadTime = performance.now() - this.pageLoadStart;
            
            this.logPerformanceMetric({
                metric_type: 'page_load',
                value: loadTime,
                unit: 'ms',
                page_url: window.location.href,
                additional_data: {
                    method: 'traditional_timing',
                    user_agent: navigator.userAgent,
                    viewport: `${window.innerWidth}x${window.innerHeight}`,
                    screen: `${screen.width}x${screen.height}`
                }
            });
        });
        
        // Monitor DOM content loaded
        document.addEventListener('DOMContentLoaded', () => {
            const domReadyTime = performance.now() - this.pageLoadStart;
            
            this.logPerformanceMetric({
                metric_type: 'dom_ready',
                value: domReadyTime,
                unit: 'ms',
                page_url: window.location.href
            });
        });
    }
    
    /**
     * Monitor user interactions
     */
    monitorUserInteractions() {
        let interactionStart = Date.now();
        let interactionCount = 0;
        
        const interactionEvents = ['click', 'input', 'submit', 'change', 'scroll'];
        
        interactionEvents.forEach(eventType => {
            document.addEventListener(eventType, () => {
                interactionCount++;
                const now = Date.now();
                
                // Log interaction every 10 events or every 5 seconds
                if (interactionCount % 10 === 0 || (now - interactionStart) > 5000) {
                    this.logPerformanceMetric({
                        metric_type: 'user_interaction',
                        value: interactionCount,
                        unit: 'count',
                        page_url: window.location.href,
                        additional_data: {
                            event_type: eventType,
                            time_period: now - interactionStart
                        }
                    });
                    
                    interactionStart = now;
                    interactionCount = 0;
                }
            }, { passive: true });
        });
    }
    
    /**
     * Monitor AJAX requests
     */
    monitorAjaxRequests() {
        // Intercept XMLHttpRequest
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;
        
        XMLHttpRequest.prototype.open = function(method, url, ...args) {
            this._monitoringData = {
                method: method,
                url: url,
                startTime: performance.now()
            };
            return originalXHROpen.apply(this, [method, url, ...args]);
        };
        
        XMLHttpRequest.prototype.send = function(...args) {
            if (this._monitoringData) {
                this.addEventListener('load', () => {
                    const duration = performance.now() - this._monitoringData.startTime;
                    
                    window.monitoringSystem.logPerformanceMetric({
                        metric_type: 'ajax_request',
                        value: duration,
                        unit: 'ms',
                        page_url: window.location.href,
                        additional_data: {
                            method: this._monitoringData.method,
                            url: this._monitoringData.url,
                            status: this.status,
                            response_size: this.responseText?.length || 0
                        }
                    });
                });
            }
            return originalXHRSend.apply(this, args);
        };
        
        // Intercept fetch API
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            const startTime = performance.now();
            const [url, options = {}] = args;
            
            return originalFetch.apply(this, args).then(response => {
                const duration = performance.now() - startTime;
                
                window.monitoringSystem.logPerformanceMetric({
                    metric_type: 'fetch_request',
                    value: duration,
                    unit: 'ms',
                    page_url: window.location.href,
                    additional_data: {
                        method: options.method || 'GET',
                        url: url,
                        status: response.status,
                        response_size: 0 // Would need to clone response to get size
                    }
                });
                
                return response;
            });
        };
    }
    
    /**
     * Log a client-side error
     */
    logClientError(errorData) {
        // Add timestamp and session info
        const enrichedError = {
            ...errorData,
            timestamp: new Date().toISOString(),
            session_id: this.getSessionId(),
            user_id: this.getUserId()
        };
        
        this.errorQueue.push(enrichedError);
        
        // Flush immediately for critical errors
        if (errorData.level === 'CRITICAL' || errorData.level === 'ERROR') {
            this.flushErrorQueue();
        }
        
        // Limit queue size
        if (this.errorQueue.length > this.maxQueueSize) {
            this.errorQueue.shift();
        }
    }
    
    /**
     * Log a performance metric
     */
    logPerformanceMetric(metricData) {
        // Add timestamp and session info
        const enrichedMetric = {
            ...metricData,
            timestamp: new Date().toISOString(),
            session_id: this.getSessionId(),
            user_id: this.getUserId()
        };
        
        this.performanceQueue.push(enrichedMetric);
        
        // Limit queue size
        if (this.performanceQueue.length > this.maxQueueSize) {
            this.performanceQueue.shift();
        }
    }
    
    /**
     * Set up periodic flushing of queues
     */
    setupPeriodicFlushing() {
        setInterval(() => {
            this.flushErrorQueue();
            this.flushPerformanceQueue();
        }, this.flushInterval);
        
        // Flush on page unload
        window.addEventListener('beforeunload', () => {
            this.flushErrorQueue();
            this.flushPerformanceQueue();
        });
    }
    
    /**
     * Flush error queue to API
     */
    async flushErrorQueue() {
        if (this.errorQueue.length === 0) return;
        
        const errorsToSend = [...this.errorQueue];
        this.errorQueue = [];
        
        for (const error of errorsToSend) {
            try {
                await this.sendToAPI('log_client_error', error);
            } catch (e) {
                console.warn('Failed to send error to monitoring API:', e);
                // Re-add to queue for retry
                this.errorQueue.unshift(error);
            }
        }
    }
    
    /**
     * Flush performance queue to API
     */
    async flushPerformanceQueue() {
        if (this.performanceQueue.length === 0) return;
        
        const metricsToSend = [...this.performanceQueue];
        this.performanceQueue = [];
        
        for (const metric of metricsToSend) {
            try {
                await this.sendToAPI('log_performance_metric', metric);
            } catch (e) {
                console.warn('Failed to send metric to monitoring API:', e);
                // Re-add to queue for retry
                this.performanceQueue.unshift(metric);
            }
        }
    }
    
    /**
     * Send data to monitoring API
     */
    async sendToAPI(action, data) {
        const formData = new FormData();
        formData.append('action', action);
        
        // Add all data fields
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined) {
                formData.append(key, data[key]);
            }
        });
        
        const response = await fetch(this.apiUrl, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'API call failed');
        }
        
        return result;
    }
    
    /**
     * Get browser information
     */
    getBrowserInfo() {
        return {
            user_agent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            cookie_enabled: navigator.cookieEnabled,
            on_line: navigator.onLine,
            connection: navigator.connection ? {
                effective_type: navigator.connection.effectiveType,
                downlink: navigator.connection.downlink,
                rtt: navigator.connection.rtt
            } : null
        };
    }
    
    /**
     * Get page load time
     */
    getPageLoadTime() {
        return performance.now() - this.pageLoadStart;
    }
    
    /**
     * Get caller line number for console interceptor
     */
    getCallerLine() {
        try {
            throw new Error();
        } catch (e) {
            const stack = e.stack.split('\n');
            // Find the first line that's not from our monitoring code
            for (let i = 0; i < stack.length; i++) {
                if (!stack[i].includes('monitoring.js') && !stack[i].includes('MonitoringSystem')) {
                    const match = stack[i].match(/:(\d+):/);
                    return match ? parseInt(match[1]) : 0;
                }
            }
        }
        return 0;
    }
    
    /**
     * Get session ID from cookie
     */
    getSessionId() {
        return document.cookie
            .split('; ')
            .find(row => row.startsWith('storeall_session='))
            ?.split('=')[1] || 'unknown';
    }
    
    /**
     * Get user ID from page data or session
     */
    getUserId() {
        // Try to get from page data first
        if (window.currentUser && window.currentUser.id) {
            return window.currentUser.id;
        }
        
        // Fallback to session storage or cookie
        return sessionStorage.getItem('user_id') || 'unknown';
    }
    
    /**
     * Get system health status
     */
    async getSystemHealth() {
        try {
            const response = await fetch(`${this.apiUrl}?action=get_system_health`);
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (e) {
            console.warn('Failed to get system health:', e);
            return null;
        }
    }
    
    /**
     * Get performance metrics
     */
    async getPerformanceMetrics() {
        try {
            const response = await fetch(`${this.apiUrl}?action=get_performance_metrics`);
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (e) {
            console.warn('Failed to get performance metrics:', e);
            return null;
        }
    }
    
    /**
     * Get error logs
     */
    async getErrorLogs(filters = {}) {
        try {
            const params = new URLSearchParams({ action: 'get_error_logs', ...filters });
            const response = await fetch(`${this.apiUrl}?${params}`);
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (e) {
            console.warn('Failed to get error logs:', e);
            return null;
        }
    }
}

// Initialize monitoring system when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.monitoringSystem = new MonitoringSystem();
    
    // Log that monitoring is active
    console.log('StoreAll.io monitoring system initialized');
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MonitoringSystem;
}

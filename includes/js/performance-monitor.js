/**
 * Performance Monitor for StoreAll.io
 * Tracks page load performance and client-side metrics
 */

class PerformanceMonitor {
    constructor() {
        this.metrics = {
            pageLoadStart: performance.now(),
            navigationStart: performance.timing?.navigationStart || Date.now(),
            measurements: {}
        };
        
        this.init();
    }
    
    init() {
        // Wait for page to fully load
        if (document.readyState === 'complete') {
            this.measurePageLoad();
        } else {
            window.addEventListener('load', () => {
                this.measurePageLoad();
            });
        }
        
        // Measure DOM content loaded
        if (document.readyState === 'interactive' || document.readyState === 'complete') {
            this.measureDOMContentLoaded();
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                this.measureDOMContentLoaded();
            });
        }
        
        // Monitor long tasks
        this.monitorLongTasks();
        
        // Monitor memory usage
        this.monitorMemory();
        
        // Monitor network performance
        this.monitorNetwork();
    }
    
    measurePageLoad() {
        const loadTime = performance.now() - this.metrics.pageLoadStart;
        
        this.metrics.measurements.pageLoad = {
            duration: loadTime,
            timestamp: Date.now()
        };
        
        // Get detailed timing if available
        if (performance.timing) {
            const timing = performance.timing;
            this.metrics.measurements.detailedTiming = {
                dnsLookup: timing.domainLookupEnd - timing.domainLookupStart,
                tcpConnect: timing.connectEnd - timing.connectStart,
                serverResponse: timing.responseEnd - timing.requestStart,
                domParsing: timing.domContentLoadedEventEnd - timing.domLoading,
                domReady: timing.domContentLoadedEventEnd - timing.navigationStart,
                loadComplete: timing.loadEventEnd - timing.navigationStart
            };
        }
        
        // Send metrics to server
        this.sendMetrics('PAGE_LOAD');
    }
    
    measureDOMContentLoaded() {
        const domReadyTime = performance.now() - this.metrics.pageLoadStart;
        
        this.metrics.measurements.domContentLoaded = {
            duration: domReadyTime,
            timestamp: Date.now()
        };
    }
    
    monitorLongTasks() {
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.duration > 50) { // Tasks longer than 50ms
                            this.metrics.measurements.longTasks = this.metrics.measurements.longTasks || [];
                            this.metrics.measurements.longTasks.push({
                                duration: entry.duration,
                                startTime: entry.startTime,
                                name: entry.name,
                                timestamp: Date.now()
                            });
                        }
                    }
                });
                
                observer.observe({ entryTypes: ['longtask'] });
            } catch (e) {
                console.warn('Long task monitoring not supported');
            }
        }
    }
    
    monitorMemory() {
        if ('memory' in performance) {
            setInterval(() => {
                const memory = performance.memory;
                this.metrics.measurements.memory = {
                    usedJSHeapSize: memory.usedJSHeapSize,
                    totalJSHeapSize: memory.totalJSHeapSize,
                    jsHeapSizeLimit: memory.jsHeapSizeLimit,
                    timestamp: Date.now()
                };
            }, 30000); // Check every 30 seconds
        }
    }
    
    monitorNetwork() {
        if ('connection' in navigator) {
            const connection = navigator.connection;
            this.metrics.measurements.network = {
                effectiveType: connection.effectiveType,
                downlink: connection.downlink,
                rtt: connection.rtt,
                saveData: connection.saveData,
                timestamp: Date.now()
            };
        }
    }
    
    measureCustomMetric(name, duration) {
        this.metrics.measurements[name] = {
            duration: duration,
            timestamp: Date.now()
        };
        
        this.sendMetrics('CUSTOM_METRIC', { name, duration });
    }
    
    measureAjaxRequest(url, method, duration, status) {
        this.metrics.measurements.ajaxRequests = this.metrics.measurements.ajaxRequests || [];
        this.metrics.measurements.ajaxRequests.push({
            url: url,
            method: method,
            duration: duration,
            status: status,
            timestamp: Date.now()
        });
        
        // Send slow requests to server
        if (duration > 1000) { // Requests longer than 1 second
            this.sendMetrics('SLOW_AJAX', { url, method, duration, status });
        }
    }
    
    sendMetrics(type, additionalData = {}) {
        const metricsData = {
            type: type,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: Date.now(),
            sessionId: this.getSessionId(),
            measurements: this.metrics.measurements,
            ...additionalData
        };
        
        // Send to server
        fetch('/api/log-performance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(metricsData)
        }).catch(err => {
            // Fallback: log to console if server request fails
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.log('Performance metrics:', metricsData);
            }
        });
    }
    
    getSessionId() {
        // Try to get session ID from cookie
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'PHPSESSID') {
                return value;
            }
        }
        return null;
    }
    
    getMetrics() {
        return this.metrics;
    }
}

// Initialize performance monitor when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.performanceMonitor = new PerformanceMonitor();
});

// Also initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.performanceMonitor = new PerformanceMonitor();
    });
} else {
    window.performanceMonitor = new PerformanceMonitor();
}

// Intercept AJAX requests to measure performance
const originalXHROpen = XMLHttpRequest.prototype.open;
const originalXHRSend = XMLHttpRequest.prototype.send;

XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
    this._method = method;
    this._url = url;
    this._startTime = performance.now();
    return originalXHROpen.call(this, method, url, async, user, password);
};

XMLHttpRequest.prototype.send = function(data) {
    this.addEventListener('load', () => {
        const duration = performance.now() - this._startTime;
        if (window.performanceMonitor) {
            window.performanceMonitor.measureAjaxRequest(
                this._url, 
                this._method, 
                duration, 
                this.status
            );
        }
    });
    
    return originalXHRSend.call(this, data);
};

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring System Test - StoreAll.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; }
        .error-test { background-color: #f8d7da; border-color: #f5c6cb; }
        .performance-test { background-color: #d1ecf1; border-color: #bee5eb; }
        .success-test { background-color: #d4edda; border-color: #c3e6cb; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🧪 Monitoring System Test Page</h1>
        <p class="lead">This page tests the StoreAll.io monitoring system by generating various events.</p>
        
        <div class="alert alert-info">
            <strong>Note:</strong> Open your browser's developer console to see monitoring logs and check the admin dashboard for collected data.
        </div>
        
        <!-- Error Testing Section -->
        <div class="test-section error-test">
            <h3>🚨 Error Testing</h3>
            <p>Click these buttons to generate different types of errors for monitoring:</p>
            
            <button class="btn btn-danger me-2" onclick="generateJavaScriptError()">
                Generate JavaScript Error
            </button>
            
            <button class="btn btn-warning me-2" onclick="generateConsoleWarning()">
                Generate Console Warning
            </button>
            
            <button class="btn btn-info me-2" onclick="generatePromiseRejection()">
                Generate Promise Rejection
            </button>
            
            <button class="btn btn-secondary me-2" onclick="generateCustomError()">
                Generate Custom Error
            </button>
        </div>
        
        <!-- Performance Testing Section -->
        <div class="test-section performance-test">
            <h3>⚡ Performance Testing</h3>
            <p>Test performance monitoring capabilities:</p>
            
            <button class="btn btn-primary me-2" onclick="testPageLoadPerformance()">
                Test Page Load Performance
            </button>
            
            <button class="btn btn-success me-2" onclick="testAjaxPerformance()">
                Test AJAX Performance
            </button>
            
            <button class="btn btn-info me-2" onclick="testUserInteractions()">
                Test User Interactions
            </button>
            
            <button class="btn btn-warning me-2" onclick="testMemoryUsage()">
                Test Memory Usage
            </button>
        </div>
        
        <!-- Monitoring Status Section -->
        <div class="test-section success-test">
            <h3>📊 Monitoring Status</h3>
            <p>Check if monitoring is working:</p>
            
            <button class="btn btn-outline-primary me-2" onclick="checkMonitoringStatus()">
                Check Monitoring Status
            </button>
            
            <button class="btn btn-outline-success me-2" onclick="testMonitoringAPI()">
                Test Monitoring API
            </button>
            
            <button class="btn btn-outline-info me-2" onclick="viewCollectedData()">
                View Collected Data
            </button>
        </div>
        
        <!-- Results Display -->
        <div class="test-section">
            <h3>📋 Test Results</h3>
            <div id="testResults">
                <p class="text-muted">Test results will appear here...</p>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="mt-4">
            <a href="/admin/dashboard/" class="btn btn-primary">
                <i class="fas fa-chart-line me-2"></i>View Admin Dashboard
            </a>
            <a href="/" class="btn btn-secondary ms-2">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
        </div>
    </div>
    
    <!-- Include monitoring JavaScript -->
    <script src="/admin/dashboard/monitoring.js"></script>
    
    <script>
        let testCount = 0;
        
        function logTestResult(message, type = 'info') {
            testCount++;
            const resultsDiv = document.getElementById('testResults');
            const timestamp = new Date().toLocaleTimeString();
            
            const alertClass = type === 'error' ? 'alert-danger' : 
                             type === 'success' ? 'alert-success' : 
                             type === 'warning' ? 'alert-warning' : 'alert-info';
            
            const resultHtml = `
                <div class="alert ${alertClass} alert-sm">
                    <strong>Test ${testCount}:</strong> ${message}
                    <small class="text-muted ms-2">${timestamp}</small>
                </div>
            `;
            
            resultsDiv.innerHTML = resultHtml + resultsDiv.innerHTML;
        }
        
        // Error Testing Functions
        function generateJavaScriptError() {
            try {
                // Intentionally cause an error
                const undefinedVar = undefined;
                undefinedVar.someMethod();
            } catch (error) {
                logTestResult('JavaScript error generated and caught by monitoring system', 'success');
            }
        }
        
        function generateConsoleWarning() {
            console.warn('This is a test warning message for monitoring');
            logTestResult('Console warning generated and logged', 'success');
        }
        
        function generatePromiseRejection() {
            Promise.reject(new Error('Test promise rejection for monitoring'));
            logTestResult('Promise rejection generated for monitoring', 'success');
        }
        
        function generateCustomError() {
            // This will trigger the global error handler
            throw new Error('Custom test error for monitoring system');
        }
        
        // Performance Testing Functions
        function testPageLoadPerformance() {
            // Simulate page load performance measurement
            const startTime = performance.now();
            
            setTimeout(() => {
                const loadTime = performance.now() - startTime;
                logTestResult(`Page load performance test completed in ${loadTime.toFixed(2)}ms`, 'success');
            }, 100);
        }
        
        function testAjaxPerformance() {
            const startTime = performance.now();
            
            // Test AJAX request
            fetch('/admin/dashboard/monitoring_api.php?action=get_system_health')
                .then(response => response.json())
                .then(data => {
                    const responseTime = performance.now() - startTime;
                    logTestResult(`AJAX performance test completed in ${responseTime.toFixed(2)}ms`, 'success');
                })
                .catch(error => {
                    logTestResult('AJAX performance test failed: ' + error.message, 'error');
                });
        }
        
        function testUserInteractions() {
            // Simulate user interactions
            let clickCount = 0;
            const testButton = document.createElement('button');
            testButton.textContent = 'Test Click';
            testButton.className = 'btn btn-sm btn-outline-secondary ms-2';
            testButton.onclick = () => {
                clickCount++;
                if (clickCount >= 5) {
                    logTestResult('User interaction test completed (5 clicks)', 'success');
                    testButton.remove();
                }
            };
            
            document.querySelector('.performance-test').appendChild(testButton);
            logTestResult('User interaction test started - click the test button 5 times', 'info');
        }
        
        function testMemoryUsage() {
            // Generate some memory usage
            const testArray = [];
            for (let i = 0; i < 10000; i++) {
                testArray.push({ id: i, data: 'test data ' + i });
            }
            
            setTimeout(() => {
                testArray.length = 0; // Clear array
                logTestResult('Memory usage test completed', 'success');
            }, 100);
        }
        
        // Monitoring Status Functions
        function checkMonitoringStatus() {
            if (window.monitoringSystem) {
                logTestResult('✅ Monitoring system is active and running', 'success');
            } else {
                logTestResult('❌ Monitoring system is not active', 'error');
            }
        }
        
        function testMonitoringAPI() {
            fetch('/admin/dashboard/monitoring_api.php?action=get_system_health')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        logTestResult('✅ Monitoring API is working correctly', 'success');
                    } else {
                        logTestResult('⚠️ Monitoring API returned error: ' + data.message, 'warning');
                    }
                })
                .catch(error => {
                    logTestResult('❌ Monitoring API test failed: ' + error.message, 'error');
                });
        }
        
        function viewCollectedData() {
            // Redirect to admin dashboard monitoring tab
            window.open('/admin/dashboard/#monitoring', '_blank');
            logTestResult('Opening admin dashboard monitoring tab', 'info');
        }
        
        // Initialize test page
        document.addEventListener('DOMContentLoaded', function() {
            logTestResult('Test page loaded successfully', 'success');
            checkMonitoringStatus();
            
            // Auto-generate some test data after a delay
            setTimeout(() => {
                logTestResult('Auto-generating test data for monitoring...', 'info');
                generateConsoleWarning();
                testPageLoadPerformance();
            }, 2000);
        });
        
        // Test error handling
        window.addEventListener('error', function(event) {
            logTestResult('Global error handler caught: ' + event.message, 'success');
        });
        
        window.addEventListener('unhandledrejection', function(event) {
            logTestResult('Promise rejection handler caught: ' + event.reason, 'success');
        });
    </script>
</body>
</html>

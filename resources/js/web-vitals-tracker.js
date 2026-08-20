/**
 * Web Vitals Tracking Script
 * Tracks LCP, FID, CLS, INP, and TTFB metrics
 */

(function() {
    'use strict';

    const TRACKING_ENDPOINT = '/admin/web-vitals/store';
    const DEBOUNCE_DELAY = 1000;

    // Generate or retrieve session ID
    function getSessionId() {
        let sessionId = sessionStorage.getItem('web_vitals_session_id');
        if (!sessionId) {
            sessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
            sessionStorage.setItem('web_vitals_session_id', sessionId);
        }
        return sessionId;
    }

    // Get browser and device metadata
    function getMetadata() {
        return {
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            screenResolution: `${screen.width}x${screen.height}`,
            viewportSize: `${window.innerWidth}x${window.innerHeight}`,
            connectionType: navigator.connection ? navigator.connection.effectiveType : 'unknown',
            deviceMemory: navigator.deviceMemory || 'unknown',
            hardwareConcurrency: navigator.hardwareConcurrency || 'unknown',
        };
    }

    // Debounce function to prevent duplicate submissions
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Send metric to server
    function sendMetric(metricType, value, url = null) {
        const data = {
            metric_type: metricType,
            value: value,
            url: url || window.location.href,
            session_id: getSessionId(),
            metadata: getMetadata(),
            measured_at: new Date().toISOString(),
        };

        // Use sendBeacon for better reliability on page unload
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
            navigator.sendBeacon(TRACKING_ENDPOINT, blob);
        } else {
            // Fallback to fetch
            fetch(TRACKING_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(data),
                keepalive: true,
            }).catch(console.error);
        }
    }

    // Track Largest Contentful Paint (LCP)
    function trackLCP() {
        if (!('PerformanceObserver' in window)) return;

        const observer = new PerformanceObserver((entryList) => {
            const entries = entryList.getEntries();
            const lastEntry = entries[entries.length - 1];
            
            // LCP is in milliseconds, convert to seconds
            const lcpValue = lastEntry.startTime / 1000;
            
            sendMetric('LCP', lcpValue);
        });

        observer.observe({ entryTypes: ['largest-contentful-paint'] });
    }

    // Track First Input Delay (FID)
    function trackFID() {
        if (!('PerformanceObserver' in window)) return;

        const observer = new PerformanceObserver((entryList) => {
            const entries = entryList.getEntries();
            
            entries.forEach(entry => {
                // FID is in milliseconds, convert to seconds
                const fidValue = entry.processingStart - entry.startTime;
                if (fidValue > 0) {
                    sendMetric('FID', fidValue / 1000);
                }
            });
        });

        observer.observe({ entryTypes: ['first-input'] });
    }

    // Track Cumulative Layout Shift (CLS)
    function trackCLS() {
        if (!('PerformanceObserver' in window)) return;

        let clsValue = 0;
        
        const observer = new PerformanceObserver((entryList) => {
            const entries = entryList.getEntries();
            
            entries.forEach(entry => {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            });
        });

        observer.observe({ entryTypes: ['layout-shift'] });

        // Send CLS after page becomes idle or on visibility change
        const sendCLS = debounce(() => {
            if (clsValue > 0) {
                sendMetric('CLS', clsValue);
            }
        }, DEBOUNCE_DELAY);

        document.addEventListener('visibilitychange', sendCLS);
        window.addEventListener('pageshow', sendCLS);
    }

    // Track Interaction to Next Paint (INP)
    function trackINP() {
        if (!('PerformanceObserver' in window)) return;

        let inpValue = 0;
        let maxDuration = 0;

        const observer = new PerformanceObserver((entryList) => {
            const entries = entryList.getEntries();
            
            entries.forEach(entry => {
                const duration = entry.duration;
                if (duration > maxDuration) {
                    maxDuration = duration;
                    inpValue = duration;
                }
            });
        });

        observer.observe({ entryTypes: ['event', 'first-input'] });

        // Send INP periodically and on page hide
        const sendINP = debounce(() => {
            if (inpValue > 0) {
                // Convert to seconds
                sendMetric('INP', inpValue / 1000);
            }
        }, DEBOUNCE_DELAY);

        document.addEventListener('visibilitychange', sendINP);
        setInterval(sendINP, 30000); // Send every 30 seconds
    }

    // Track Time to First Byte (TTFB)
    function trackTTFB() {
        if (!('performance' in window) || !('getEntriesByType' in performance)) return;

        const navigationEntries = performance.getEntriesByType('navigation');
        
        if (navigationEntries.length > 0) {
            const navEntry = navigationEntries[0];
            // TTFB is in milliseconds, convert to seconds
            const ttfbValue = navEntry.responseStart / 1000;
            
            sendMetric('TTFB', ttfbValue);
        }
    }

    // Initialize tracking
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        // Start tracking all metrics
        trackTTFB(); // Run immediately
        trackLCP();
        trackFID();
        trackCLS();
        trackINP();

        console.log('[Web Vitals] Tracking initialized');
    }

    // Start tracking
    init();
})();

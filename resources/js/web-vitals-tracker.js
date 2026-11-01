/**
 * Core Web Vitals tracker for public pages.
 *
 * Collects LCP, CLS, INP, FID and TTFB with the PerformanceObserver API and
 * reports each metric once, when the page is hidden or unloaded. The ingest
 * endpoint is public (no session, no CSRF token), so sendBeacon() works while
 * the browser is tearing the page down.
 */

(function () {
    'use strict';

    var ENDPOINT = '/api/v1/public/web-vitals';
    var SESSION_KEY = 'web_vitals_session_id';

    var values = {};
    var flushed = false;

    function sessionId() {
        try {
            var existing = window.sessionStorage.getItem(SESSION_KEY);
            if (existing) {
                return existing;
            }

            var generated = 'sess_' + Math.random().toString(36).slice(2, 11) + '_' + Date.now();
            window.sessionStorage.setItem(SESSION_KEY, generated);

            return generated;
        } catch (error) {
            // Storage can be blocked (private mode, strict settings).
            return null;
        }
    }

    function metadata() {
        var connection = navigator.connection || {};

        return {
            userAgent: navigator.userAgent,
            language: navigator.language,
            screenResolution: screen.width + 'x' + screen.height,
            viewportSize: window.innerWidth + 'x' + window.innerHeight,
            connectionType: connection.effectiveType || 'unknown',
            deviceMemory: navigator.deviceMemory || 'unknown',
            hardwareConcurrency: navigator.hardwareConcurrency || 'unknown'
        };
    }

    function report(metricType, value) {
        if (!isFinite(value) || value <= 0) {
            return;
        }

        var payload = JSON.stringify({
            metric_type: metricType,
            value: Math.round(value * 1000) / 1000,
            url: window.location.href,
            session_id: sessionId(),
            metadata: metadata()
        });

        if (navigator.sendBeacon) {
            navigator.sendBeacon(ENDPOINT, new Blob([payload], { type: 'application/json' }));

            return;
        }

        fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: payload,
            keepalive: true
        }).catch(function () {
            // Metrics are best effort; never surface errors to the visitor.
        });
    }

    function observe(entryTypes, handler) {
        if (!('PerformanceObserver' in window)) {
            return;
        }

        try {
            var observer = new PerformanceObserver(function (list) {
                handler(list.getEntries());
            });

            observer.observe({ entryTypes: entryTypes });
        } catch (error) {
            // Unsupported entry types throw on older browsers.
        }
    }

    function trackLayoutShift() {
        observe(['layout-shift'], function (entries) {
            entries.forEach(function (entry) {
                if (!entry.hadRecentInput) {
                    values.CLS = (values.CLS || 0) + entry.value;
                }
            });
        });
    }

    function trackLcp() {
        observe(['largest-contentful-paint'], function (entries) {
            var last = entries[entries.length - 1];

            if (last) {
                values.LCP = last.startTime / 1000;
            }
        });
    }

    function trackInteractions() {
        observe(['event', 'first-input'], function (entries) {
            entries.forEach(function (entry) {
                if (!entry.duration || entry.duration <= 0) {
                    return;
                }

                if (!values.INP || entry.duration > values.INP * 1000) {
                    values.INP = entry.duration / 1000;
                }
            });
        });
    }

    function trackFirstInputDelay() {
        observe(['first-input'], function (entries) {
            entries.forEach(function (entry) {
                var delay = entry.processingStart - entry.startTime;

                if (delay > 0 && values.FID === undefined) {
                    values.FID = delay / 1000;
                }
            });
        });
    }

    function trackTtfb() {
        var navigation = (performance.getEntriesByType && performance.getEntriesByType('navigation')) || [];

        if (navigation.length > 0) {
            report('TTFB', navigation[0].responseStart / 1000);
        }
    }

    function flush() {
        if (flushed) {
            return;
        }

        flushed = true;

        Object.keys(values).forEach(function (metricType) {
            report(metricType, values[metricType]);
        });
    }

    function start() {
        trackTtfb();
        trackLcp();
        trackLayoutShift();
        trackInteractions();
        trackFirstInputDelay();

        window.addEventListener('pagehide', flush);

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                flush();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

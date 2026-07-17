(function () {
    'use strict';

    if (navigator.webdriver || !window.PerformanceObserver) {
        return;
    }

    var data = {
        url: window.location.href,
        device: window.innerWidth < 768 ? 'mobile' : 'desktop',
        recorded_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
    };
    var pending = 0;
    var sent = false;

    function send() {
        if (sent) return;
        sent = true;

        var payload = JSON.stringify(data);

        if (navigator.sendBeacon) {
            navigator.sendBeacon(advikVitals.restUrl + 'advik-optimizer/v1/vitals/ingest', payload);
        } else {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', advikVitals.restUrl + 'advik-optimizer/v1/vitals/ingest', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(payload);
        }
    }

    function trySend() {
        pending--;
        if (pending <= 0) {
            send();
        }
    }

    try {
        var lcpObs = new PerformanceObserver(function (list) {
            var entries = list.getEntries();
            if (entries.length > 0) {
                data.lcp = entries[entries.length - 1].startTime;
            }
        });
        lcpObs.observe({ type: 'largest-contentful-paint', buffered: true });
        pending++;
    } catch (e) {}

    try {
        var clsObs = new PerformanceObserver(function (list) {
            var entries = list.getEntries();
            var cls = 0;
            for (var i = 0; i < entries.length; i++) {
                if (!entries[i].hadRecentInput) {
                    cls += entries[i].value;
                }
            }
            data.cls = cls;
        });
        clsObs.observe({ type: 'layout-shift', buffered: true });
        pending++;
    } catch (e) {}

    try {
        var inpObs = new PerformanceObserver(function (list) {
            var entries = list.getEntries();
            if (entries.length > 0) {
                data.inp = entries[entries.length - 1].duration;
            }
        });
        inpObs.observe({ type: 'first-input', buffered: true });
        pending++;
    } catch (e) {}

    try {
        var ttfbEntry = performance.getEntriesByType('navigation')[0];
        if (ttfbEntry) {
            data.ttfb = ttfbEntry.responseStart - ttfbEntry.requestStart;
        }
    } catch (e) {}

    if (pending > 0) {
        setTimeout(send, 3000);
    } else {
        send();
    }
})();

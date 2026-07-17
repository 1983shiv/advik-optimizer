(function () {
    'use strict';

    var settingsPage = document.querySelector('.advik-optimizer-settings');

    if (settingsPage) {
        var savedParam = new URLSearchParams(window.location.search).get('saved');

        if (savedParam === '1') {
            var notice = document.createElement('div');
            notice.className = 'notice notice-success is-dismissible';
            notice.innerHTML = '<p>' + advikOptimizerAdmin?.settingsSaved || 'Settings saved.' + '</p>';
            document.querySelector('.advik-optimizer-settings h1')?.after(notice);
        }

        var toggles = settingsPage.querySelectorAll('.advik-toggle-trigger');
        for (var t = 0; t < toggles.length; t++) {
            toggles[t].addEventListener('change', function () {
                var target = this.getAttribute('data-target');
                var field = document.getElementById(target + '_field');
                if (field) {
                    field.style.display = this.checked ? '' : 'none';
                }
            });
        }
    }

    var isDashboard = document.querySelector('.advik-dashboard');

    if (isDashboard) {
        var deviceTabs = document.querySelectorAll('.advik-dashboard .advik-device-tab');
        for (var i = 0; i < deviceTabs.length; i++) {
            deviceTabs[i].addEventListener('click', function () {
                var device = this.getAttribute('data-device');

                document.querySelectorAll('.advik-dashboard .advik-device-tab').forEach(function (t) {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                document.querySelectorAll('.advik-dashboard .advik-device-panel').forEach(function (p) {
                    p.classList.remove('active');
                });
                var panel = document.querySelector('.advik-dashboard .advik-device-panel[data-device="' + device + '"]');
                if (panel) {
                    panel.classList.add('active');
                }
            });
        }

        var rangeButtons = document.querySelectorAll('.advik-dashboard .advik-trend-range');
        for (var j = 0; j < rangeButtons.length; j++) {
            rangeButtons[j].addEventListener('click', function () {
                var range = this.getAttribute('data-range');
                var panel = this.closest('.advik-device-panel');

                panel.querySelectorAll('.advik-trend-range').forEach(function (b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                panel.querySelectorAll('.advik-sparkline').forEach(function (s) {
                    if (s.getAttribute('data-range') === range) {
                        s.classList.remove('advik-sparkline-hidden');
                    } else {
                        s.classList.add('advik-sparkline-hidden');
                    }
                });
            });
        }
    }
})();

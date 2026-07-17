(function () {
    'use strict';

    if (document.querySelector('.advik-optimizer-settings')) {
        var savedParam = new URLSearchParams(window.location.search).get('saved');

        if (savedParam === '1') {
            var notice = document.createElement('div');
            notice.className = 'notice notice-success is-dismissible';
            notice.innerHTML = '<p>' + advikOptimizerAdmin?.settingsSaved || 'Settings saved.' + '</p>';
            document.querySelector('.advik-optimizer-settings h1')?.after(notice);
        }
    }

    var tabs = document.querySelectorAll('.advik-dashboard .advik-device-tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener('click', function () {
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
})();

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
})();

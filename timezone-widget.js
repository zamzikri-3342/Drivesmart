/**
 * timezone-widget.js
 * DriveSmart – World clock widget
 *
 * Drop `<div data-timezone-widget></div>` anywhere and this script turns it
 * into a live clock with a time zone picker, backed by get_timezone.php
 * (TimeZoneDB). The seconds tick client-side every second; the server is
 * only consulted for the GMT-offset/country label and whenever the user
 * switches zones.
 */
(function () {
    var TIMEZONES = [
        { value: 'Asia/Kuala_Lumpur', label: 'Kuala Lumpur' },
        { value: 'Asia/Singapore', label: 'Singapore' },
        { value: 'Asia/Jakarta', label: 'Jakarta' },
        { value: 'Asia/Bangkok', label: 'Bangkok' },
        { value: 'Asia/Manila', label: 'Manila' },
        { value: 'Asia/Hong_Kong', label: 'Hong Kong' },
        { value: 'Asia/Shanghai', label: 'Beijing / Shanghai' },
        { value: 'Asia/Tokyo', label: 'Tokyo' },
        { value: 'Asia/Seoul', label: 'Seoul' },
        { value: 'Asia/Kolkata', label: 'Mumbai / New Delhi' },
        { value: 'Asia/Dubai', label: 'Dubai' },
        { value: 'Europe/London', label: 'London' },
        { value: 'Europe/Paris', label: 'Paris' },
        { value: 'Europe/Berlin', label: 'Berlin' },
        { value: 'Europe/Moscow', label: 'Moscow' },
        { value: 'Africa/Cairo', label: 'Cairo' },
        { value: 'Africa/Johannesburg', label: 'Johannesburg' },
        { value: 'America/New_York', label: 'New York' },
        { value: 'America/Chicago', label: 'Chicago' },
        { value: 'America/Denver', label: 'Denver' },
        { value: 'America/Los_Angeles', label: 'Los Angeles' },
        { value: 'America/Sao_Paulo', label: 'Sao Paulo' },
        { value: 'Australia/Sydney', label: 'Sydney' },
        { value: 'Australia/Perth', label: 'Perth' },
        { value: 'Pacific/Auckland', label: 'Auckland' },
        { value: 'UTC', label: 'UTC' },
    ];

    var STORAGE_KEY = 'drivesmart_timezone';
    var DEFAULT_ZONE = 'Asia/Kuala_Lumpur';

    function pickDefaultZone() {
        var saved = null;
        try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
        if (saved && TIMEZONES.some(function (tz) { return tz.value === saved; })) {
            return saved;
        }
        try {
            var detected = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if (detected && TIMEZONES.some(function (tz) { return tz.value === detected; })) {
                return detected;
            }
        } catch (e) {}
        return DEFAULT_ZONE;
    }

    function makeFormatter(zone) {
        return new Intl.DateTimeFormat('en-US', {
            timeZone: zone,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
    }

    function buildWidget(root) {
        // Plain CSS classes (see .tzw* rules in shared-styles.css) rather than
        // Tailwind utility classes, so the widget renders correctly on pages
        // that don't load Tailwind (e.g. cars.php) as well as ones that do.
        root.classList.add('tzw');

        root.innerHTML =
            '<i class="bi bi-clock"></i>' +
            '<span class="tzw-time" data-tzw-time>--:--:--</span>' +
            '<span class="tzw-meta" data-tzw-meta></span>' +
            '<select class="tzw-select" data-tzw-select></select>';

        var select = root.querySelector('[data-tzw-select]');
        TIMEZONES.forEach(function (tz) {
            var opt = document.createElement('option');
            opt.value = tz.value;
            opt.textContent = tz.label;
            select.appendChild(opt);
        });

        var timeEl = root.querySelector('[data-tzw-time]');
        var metaEl = root.querySelector('[data-tzw-meta]');
        var zone = pickDefaultZone();
        var formatter = makeFormatter(zone);
        select.value = zone;

        function tick() {
            timeEl.textContent = formatter.format(new Date());
        }

        function loadMeta(tz) {
            metaEl.textContent = '';
            fetch('get_timezone.php?zone=' + encodeURIComponent(tz))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) return;
                    var offsetHrs = data.gmtOffset / 3600;
                    var sign = offsetHrs >= 0 ? '+' : '';
                    var parts = [];
                    if (data.countryName) parts.push(data.countryName);
                    parts.push('GMT' + sign + offsetHrs);
                    metaEl.textContent = parts.join(' · ');
                })
                .catch(function () {});
        }

        function setZone(tz) {
            zone = tz;
            formatter = makeFormatter(tz);
            tick();
            loadMeta(tz);
            try { localStorage.setItem(STORAGE_KEY, tz); } catch (e) {}
        }

        select.addEventListener('change', function () { setZone(select.value); });

        setZone(zone);
        setInterval(tick, 1000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var roots = document.querySelectorAll('[data-timezone-widget]');
        roots.forEach(buildWidget);
    });
})();

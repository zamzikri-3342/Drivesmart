/**
 * fuel-price-widget.js
 * DriveSmart – Live Malaysia fuel price panel
 *
 * Populates the "This Week's Fuel Prices" card (see index.html / finance.php)
 * by calling fuel_price.php, which proxies the official weekly RON95/RON97/
 * Diesel ceiling prices published on data.gov.my.
 */
(function () {
    // Fetched once and shared: other scripts on the same page (e.g. the
    // commute fuel-cost estimator in finance.php) can do
    // `window.DriveSmartFuelPrice.then(data => ...)` to reuse this live
    // RON95/RON97/Diesel reading instead of re-fetching or hardcoding a price.
    window.DriveSmartFuelPrice = fetch('fuel_price.php')
        .then(function (r) { return r.json(); })
        .catch(function () { return { success: false, error: 'Network error while loading fuel prices.' }; });

    function formatChange(value) {
        if (value === null || value === undefined || value === 0) {
            return '<span class="text-slate-400">No change vs last week</span>';
        }
        var up = value > 0;
        var arrow = up ? '▲' : '▼';
        // Fuel: a price rise is bad news (red), a drop is good news (green).
        var colorClass = up ? 'text-red-600' : 'text-emerald-600';
        return '<span class="' + colorClass + '">' + arrow + ' RM ' + Math.abs(value).toFixed(2) + ' vs last week</span>';
    }

    function formatDate(dateStr) {
        var d = new Date(dateStr + 'T00:00:00');
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-MY', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var loading = document.getElementById('fuelPriceLoading');
        var errorEl = document.getElementById('fuelPriceError');
        var grid = document.getElementById('fuelPriceGrid');
        var dateEl = document.getElementById('fuelPriceDate');

        // Skip entirely on pages that don't include the fuel price card.
        if (!loading || !grid) return;

        window.DriveSmartFuelPrice
            .then(function (data) {
                loading.classList.add('hidden');

                if (!data.success) {
                    errorEl.textContent = data.error || 'Could not load fuel prices right now.';
                    errorEl.classList.remove('hidden');
                    if (dateEl) dateEl.textContent = 'Unavailable';
                    return;
                }

                if (dateEl) dateEl.textContent = 'Effective ' + formatDate(data.date);

                document.getElementById('ron95Price').textContent = 'RM ' + data.prices.ron95.toFixed(2);
                document.getElementById('ron97Price').textContent = 'RM ' + data.prices.ron97.toFixed(2);
                document.getElementById('dieselPrice').textContent = 'RM ' + data.prices.diesel.toFixed(2);

                var change = data.change || {};
                document.getElementById('ron95Change').innerHTML = formatChange(change.ron95);
                document.getElementById('ron97Change').innerHTML = formatChange(change.ron97);
                document.getElementById('dieselChange').innerHTML = formatChange(change.diesel);

                grid.classList.remove('hidden');
            })
            .catch(function () {
                loading.classList.add('hidden');
                errorEl.textContent = 'Network error while loading fuel prices.';
                errorEl.classList.remove('hidden');
                if (dateEl) dateEl.textContent = 'Unavailable';
            });
    });
})();

/**
 * top-cars-widget.js
 * DriveSmart – Malaysia's Top 10 Most Registered Cars widget
 *
 * Populates the "Top 10 Registered Cars" card (see index.html) by calling
 * top_cars.php, which aggregates JPJ's official year-to-date car
 * registration transactions (data.gov.my) into a maker+model leaderboard.
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var loading = document.getElementById('topCarsLoading');
        var errorEl = document.getElementById('topCarsError');
        var list = document.getElementById('topCarsList');
        var yearEl = document.getElementById('topCarsYear');

        // Skip entirely on pages that don't include the top cars card.
        if (!loading || !list) return;

        function renderCar(car) {
            var li = document.createElement('li');
            li.className = 'flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2';

            var left = document.createElement('div');
            left.className = 'flex items-center gap-3 min-w-0';

            var badge = document.createElement('span');
            badge.className = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-smart-blue text-white text-xs font-bold';
            badge.textContent = car.rank;

            var name = document.createElement('p');
            name.className = 'text-sm font-bold text-smart-blue truncate';
            name.textContent = car.maker + ' ' + car.model;

            left.appendChild(badge);
            left.appendChild(name);

            var count = document.createElement('span');
            count.className = 'text-xs font-semibold text-brandSlate whitespace-nowrap';
            count.textContent = Number(car.count).toLocaleString('en-MY') + ' units';

            li.appendChild(left);
            li.appendChild(count);
            return li;
        }

        fetch('top_cars.php')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                loading.classList.add('hidden');

                if (!data.success || !Array.isArray(data.cars) || data.cars.length === 0) {
                    errorEl.textContent = data.error || 'Could not load the top registered cars list right now.';
                    errorEl.classList.remove('hidden');
                    if (yearEl) yearEl.textContent = 'Unavailable';
                    return;
                }

                if (yearEl) yearEl.textContent = data.year + ' Year-to-Date';

                data.cars.forEach(function (car) {
                    list.appendChild(renderCar(car));
                });

                list.classList.remove('hidden');
            })
            .catch(function () {
                loading.classList.add('hidden');
                errorEl.textContent = 'Network error while loading the top registered cars list.';
                errorEl.classList.remove('hidden');
                if (yearEl) yearEl.textContent = 'Unavailable';
            });
    });
})();

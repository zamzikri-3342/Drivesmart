<?php
session_start();
require_once __DIR__ . '/functions.php';

$dbError = null;
$pdo = getDbConnection($dbError);
$loadedFromDb = false;
$cars = [];
$errorMessage = null;

if ($pdo === null) {
    $errorMessage = 'Database connection failed';
    if (!empty($dbError)) {
        $errorMessage .= ': ' . $dbError;
    }
    $errorMessage .= '. Please update config.php with correct MySQL credentials and ensure the database exists.';
} else {
    try {
        $cars = loadCarDataFromDb($pdo);
        $loadedFromDb = true;
    } catch (Exception $e) {
        $errorMessage = 'Error loading cars from database: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars Table Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="mb-4 d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h3">Cars Table Viewer</h1>
                <p class="text-muted">This page shows rows currently saved in the <code>cars</code> MySQL table.</p>
            </div>
            <a href="admindashboard.php" class="btn btn-secondary">
                &larr; Back to Dashboard
            </a>
        </div>

        <?php if ($errorMessage !== null): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage, ENT_QUOTES) ?></div>
        <?php else: ?>
            <div class="alert alert-success">Loaded <?= count($cars) ?> rows from the <code>cars</code> table.</div>
            <?php if (empty($cars)): ?>
                <div class="alert alert-warning">The table is empty. Run <code>php import_cars.php</code> to import cars.csv.</div>
            <?php else: ?>
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-filter="all" onclick="filterTable('all')">
                            All Cars (<?= count($cars) ?>)
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-filter="non-ev" onclick="filterTable('non-ev')">
                            Non-EV Cars (<span id="non-ev-count">0</span>)
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-filter="ev" onclick="filterTable('ev')">
                            EV Cars (<span id="ev-count">0</span>)
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle" id="carsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Body Type</th>
                                <th>Engine</th>
                                <th>Fuel</th>
                                <th>Price</th>
                                <th>Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $index => $car): 
                                $fuelType = strtolower($car['fuel']);
                                $isEV = in_array($fuelType, ['ev', 'electric', 'battery']);
                                $filterClass = $isEV ? 'ev-car' : 'non-ev-car';
                            ?>
                                <tr class="car-row <?= $filterClass ?>" data-fuel="<?= htmlspecialchars($fuelType, ENT_QUOTES) ?>">
                                    <td class="row-number"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($car['brand'], ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($car['model'], ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($car['body_type'], ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($car['engine'], ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($car['fuel'], ENT_QUOTES) ?></td>
                                    <td>RM <?= formatMoney((float) $car['price']) ?></td>
                                    <td><a href="<?= strpos($car['link'], 'http') === 0 ? htmlspecialchars($car['link'], ENT_QUOTES) : 'https://' . htmlspecialchars($car['link'], ENT_QUOTES) ?>" target="_blank">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function initializeCounts() {
            let evCount = 0;
            let nonEvCount = 0;
            
            document.querySelectorAll('.car-row').forEach(row => {
                if (row.classList.contains('ev-car')) {
                    evCount++;
                } else if (row.classList.contains('non-ev-car')) {
                    nonEvCount++;
                }
            });
            
            document.getElementById('ev-count').textContent = evCount;
            document.getElementById('non-ev-count').textContent = nonEvCount;
        }
        
        function filterTable(filterType) {
            const rows = document.querySelectorAll('.car-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                let shouldShow = false;
                
                if (filterType === 'all') {
                    shouldShow = true;
                } else if (filterType === 'non-ev') {
                    shouldShow = row.classList.contains('non-ev-car');
                } else if (filterType === 'ev') {
                    shouldShow = row.classList.contains('ev-car');
                }
                
                row.style.display = shouldShow ? '' : 'none';
                
                if (shouldShow) {
                    visibleCount++;
                    row.querySelector('.row-number').textContent = visibleCount;
                }
            });
            
            // Update active button
            document.querySelectorAll('[data-filter]').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-filter="${filterType}"]`).classList.add('active');
        }
        
        // Initialize counts on page load
        document.addEventListener('DOMContentLoaded', initializeCounts);
    </script>
</body>
</html>
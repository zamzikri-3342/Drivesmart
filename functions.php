<?php

// functions kereta
function calculateRecommendation(float $netSalary, float $deposit, int $loanYears, float $annualRate): array
{
    $maxPaymentPercentage = 0.18;
    $maxMonthlyPayment = $netSalary * $maxPaymentPercentage;
    $monthlyRate = ($annualRate / 100) / 12;
    $totalPayments = $loanYears * 12;

    if ($monthlyRate == 0.0) {
        $maxLoanAmount = $maxMonthlyPayment * $totalPayments;
    } else {
        $factor = 1 - pow(1 + $monthlyRate, -$totalPayments);
        $maxLoanAmount = $maxMonthlyPayment * ($factor / $monthlyRate);
    }

    $suitableCarPrice = $maxLoanAmount + $deposit;
    return [$suitableCarPrice, $maxLoanAmount, $maxMonthlyPayment];
}

function calculateMalaysianRoadTax(string $engineStr, string $fuelStr): float
{
    $engineStr = strtolower($engineStr);
    $fuelStr = strtolower($fuelStr);

    if (strpos($engineStr, 'ev') !== false || strpos($fuelStr, 'electric') !== false) {
        return 0.00;
    }

    preg_match('/[0-9.]+(?:\s*(?:cc|l))?/i', $engineStr, $matches);
    if (empty($matches)) {
        return 0.00;
    }

    $rawNum = (float)$matches[0];

    if ($rawNum < 10.0 || strpos($engineStr, 'l') !== false) {
        $cc = round($rawNum * 1000);
    } else {
        $cc = round($rawNum);
    }

    if ($cc <= 1000) return 20.00;
    if ($cc <= 1200) return 55.00;
    if ($cc <= 1400) return 70.00;
    if ($cc <= 1600) return 90.00;
    if ($cc <= 1800) {
        return 200.00 + (($cc - 1600) * 0.40);
    }
    if ($cc <= 2000) {
        return 280.00 + (($cc - 1800) * 0.50);
    }
    if ($cc <= 2500) {
        return 380.00 + (($cc - 2000) * 1.00);
    }
    if ($cc <= 3000) {
        return 880.00 + (($cc - 2500) * 2.50);
    }

    return 2130.00 + (($cc - 3000) * 4.50);
}

function getDbConnection(?string &$error = null): ?PDO
{
    $error = null;
    $configFile = __DIR__ . '/config.php';
    if (!file_exists($configFile)) {
        $error = 'Missing config.php file.';
        return null;
    }

    require_once $configFile;

    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        $error = 'Database constants are not defined correctly in config.php.';
        return null;
    }

    $host = DB_HOST;
    $dbname = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;
    $port = defined('DB_PORT') ? DB_PORT : 3306;

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        $error = $e->getMessage();
        return null;
    }
}

function createCarTable(PDO $pdo): void
{
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(255) NOT NULL,
    model VARCHAR(255) NOT NULL,
    body_type VARCHAR(100) NOT NULL,
    engine VARCHAR(100) NOT NULL,
    fuel VARCHAR(100) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    link VARCHAR(1024) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
    $pdo->exec($sql);
}

function loadCarDataFromDb(PDO $pdo): array
{
    $sql = <<<SQL
SELECT
    c.id AS car_id,
    c.brand,
    c.model,
    c.body_type,
    c.image_path,
    c.fuel,
    c.price,
    c.link,
    cfe.engine_cc,
    cfe.km_per_liter,
    cfe.full_tank_liters,
    ev.power_output_kw
FROM cars c
LEFT JOIN car_fuel_efficiency cfe ON cfe.car_id = c.id
LEFT JOIN ev_cars ev ON ev.id = c.id
WHERE c.price IS NOT NULL
SQL;

    try {
        $stmt = $pdo->query($sql);
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'car_fuel_efficiency') || str_contains($e->getMessage(), 'ev_cars') || str_contains($e->getMessage(), 'Base table or view not found')) {
            $stmt = $pdo->query('SELECT id AS car_id, brand, model, body_type, image_path, fuel, price, link FROM cars WHERE price IS NOT NULL');
        } else {
            throw $e;
        }
    }

    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['price'] = isset($row['price']) ? (float) $row['price'] : 0.0;

        $isEv = isset($row['fuel']) && strcasecmp((string) $row['fuel'], 'EV') === 0;
        $powerOutputKw = isset($row['power_output_kw']) && $row['power_output_kw'] !== '' ? trim((string) $row['power_output_kw']) : '';

        if ($isEv) {
            $row['engine'] = $powerOutputKw !== '' ? $powerOutputKw . ' kW' : 'No data';
        } else {
            $engineCc = isset($row['engine_cc']) ? trim((string) $row['engine_cc']) : '';
            if ($engineCc === '' || $engineCc === 'null' || $engineCc === 'NULL') {
                $row['engine'] = 'No data';
            } else {
                $row['engine'] = $engineCc . ' CC';
            }
        }

        // Forward-chaining inference (engine_size, fuel_economy, recommended_use, max_distance)
        // fed from the same km_per_liter/full_tank_liters/engine_cc facts just fetched above.
        $facts = [];
        if (array_key_exists('engine_cc', $row)) {
            $facts['engine_cc'] = ($row['engine_cc'] !== null && $row['engine_cc'] !== '') ? (float) $row['engine_cc'] : null;
        }
        if (isset($row['km_per_liter']) && $row['km_per_liter'] !== '') {
            $facts['km_per_liter'] = (float) $row['km_per_liter'];
        }
        if (isset($row['full_tank_liters']) && $row['full_tank_liters'] !== '') {
            $facts['full_tank_liter'] = (float) $row['full_tank_liters'];
        }
        if (isset($row['body_type']) && $row['body_type'] !== '') {
            $facts['body_type'] = strtolower(trim((string) $row['body_type']));
        }

        $inference = runForwardChaining($facts, getFuelKnowledgeBaseRules());
        $row['engine_size'] = $inference['facts']['engine_size'] ?? null;
        $row['fuel_economy'] = $inference['facts']['fuel_economy'] ?? null;
        $row['recommended_use'] = $inference['facts']['recommended_use'] ?? null;
        $row['max_distance'] = $inference['facts']['max_distance'] ?? null;

        unset($row['engine_cc'], $row['km_per_liter'], $row['full_tank_liters'], $row['power_output_kw']);
    }

    return $rows;
}

function isMissingTableError(PDOException $e): bool
{
    $message = $e->getMessage();
    return str_contains($message, 'Base table or view not found')
        || str_contains($message, 'doesn\'t exist')
        || str_contains($message, 'Table') && str_contains($message, 'does not exist')
        || str_contains($message, '1932');
}

function syncCarsCsvToDatabase(PDO $pdo, string $csvFile = 'cars.csv'): int
{
    $cars = loadCarDataFromCsv($csvFile);
    if (empty($cars)) {
        return 0;
    }

    createCarTable($pdo);
    $pdo->beginTransaction();

    try {
        $pdo->exec('TRUNCATE TABLE cars');
        $stmt = $pdo->prepare('INSERT INTO cars (brand, model, body_type, engine, fuel, price, link) VALUES (:brand, :model, :body_type, :engine, :fuel, :price, :link)');

        foreach ($cars as $car) {
            $stmt->execute([
                ':brand' => $car['brand'],
                ':model' => $car['model'],
                ':body_type' => $car['body_type'],
                ':engine' => $car['engine'],
                ':fuel' => $car['fuel'],
                ':price' => $car['price'],
                ':link' => $car['link'],
            ]);
        }

        $pdo->commit();
        return count($cars);
    } catch (PDOException $e) {
        $pdo->rollBack();
        return 0;
    }
}

function loadCarDataFromCsv(string $csvFile = 'cars.csv'): array
{
    if (!file_exists($csvFile) || !is_readable($csvFile)) {
        return [];
    }

    $handle = fopen($csvFile, 'r');
    if ($handle === false) {
        return [];
    }

    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        return [];
    }

    $rows = [];
    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) !== count($header)) {
            continue;
        }

        $record = array_combine($header, $data);
        if ($record === false) {
            continue;
        }

        if (!isset($record['price']) || trim($record['price']) === '' || !is_numeric($record['price'])) {
            continue;
        }

        $record['price'] = (float) $record['price'];

        // Mock a default car_id for CSV fallback if needed
        $record['car_id'] = 0;

        $rows[] = $record;
    }

    fclose($handle);
    return $rows;
}

function loadCarData(string $csvFile = 'cars.csv'): array
{
    $pdo = getDbConnection($dbError);
    if ($pdo !== null) {
        try {
            return loadCarDataFromDb($pdo);
        } catch (PDOException $e) {
            if (isMissingTableError($e)) {
                createCarTable($pdo);
                $imported = syncCarsCsvToDatabase($pdo, $csvFile);
                if ($imported > 0) {
                    try {
                        return loadCarDataFromDb($pdo);
                    } catch (PDOException $inner) {
                        // If the table still cannot be read, fall back to CSV.
                    }
                }
            }
        }
    }

    return loadCarDataFromCsv($csvFile);
}

function normalizeValue(string $value): string
{
    return mb_strtolower(trim($value));
}

function filterCars(array $cars, float $maxPrice, array $sessionBodyTypes, array $engineCategories, array $selectedBodies, array $brandCategories = []): array
{
    $result = [];
    $bodyFilter = !empty($selectedBodies) ? $selectedBodies : $sessionBodyTypes;
    $bodyFilter = array_map('normalizeValue', $bodyFilter);

    $lookupEngines = array_map('normalizeValue', array_unique($engineCategories));
    $lookupBrands = array_map('normalizeValue', array_unique($brandCategories));

    foreach ($cars as $car) {
        if ($car['price'] > $maxPrice) {
            continue;
        }

        if (!empty($lookupEngines)) {
            if (!in_array(normalizeValue($car['fuel']), $lookupEngines, true)) {
                continue;
            }
        }

        if (!empty($bodyFilter)) {
            if (!in_array(normalizeValue($car['body_type']), $bodyFilter, true)) {
                continue;
            }
        }

        if (!empty($lookupBrands)) {
            if (!in_array(normalizeValue($car['brand']), $lookupBrands, true)) {
                continue;
            }
        }

        $result[] = $car;
    }

    return $result;
}

function sortCarsByPrice(array $cars, string $direction = 'asc'): array
{
    usort($cars, function ($a, $b) use ($direction) {
        if ($a['price'] === $b['price']) {
            return 0;
        }
        return ($direction === 'desc') ? ($b['price'] <=> $a['price']) : ($a['price'] <=> $b['price']);
    });
    return $cars;
}

function formatMoney(float $value, bool $noDecimals = false): string
{
    return $noDecimals
        ? number_format($value, 0, '.', ',')
        : number_format($value, 2, '.', ',');
}

/**
 * Forward-chaining rule-based system: infers max_distance, engine_size,
 * fuel_economy, and recommended_use from km_per_liter, full_tank_liter, and engine_cc.
 *
 * Knowledge base: each rule is IF (condition over known facts) THEN (derive a new fact).
 * Rules run in repeated passes until no rule fires (fixpoint), so a rule whose
 * condition depends on a fact derived by an earlier rule (e.g. recommended_use
 * depending on engine_size/fuel_economy) still fires once its inputs exist.
 */
function getFuelKnowledgeBaseRules(): array
{
    return [
        // --- Derive max_distance ---
        [
            'id' => 'R1',
            'description' => 'IF km_per_liter AND full_tank_liter are known THEN max_distance = km_per_liter x full_tank_liter',
            'condition' => function (array $facts): bool {
                return isset($facts['km_per_liter'], $facts['full_tank_liter']) && !isset($facts['max_distance']);
            },
            'action' => function (array &$facts): void {
                $facts['max_distance'] = round($facts['km_per_liter'] * $facts['full_tank_liter'], 2);
            },
        ],

        // --- Derive engine_size from engine_cc ---
        [
            'id' => 'R2',
            'description' => 'IF engine_cc is null or 0 THEN engine_size = "Electric"',
            'condition' => fn(array $facts): bool => array_key_exists('engine_cc', $facts) && (($facts['engine_cc'] === null) || $facts['engine_cc'] == 0) && !isset($facts['engine_size']),
            'action' => function (array &$facts): void {
                $facts['engine_size'] = 'Electric';
            },
        ],
        [
            'id' => 'R3',
            'description' => 'IF 0 < engine_cc <= 1000 THEN engine_size = "Small"',
            'condition' => fn(array $facts): bool => isset($facts['engine_cc']) && $facts['engine_cc'] > 0 && $facts['engine_cc'] <= 1000 && !isset($facts['engine_size']),
            'action' => function (array &$facts): void {
                $facts['engine_size'] = 'Small';
            },
        ],
        [
            'id' => 'R4',
            'description' => 'IF 1000 < engine_cc <= 1600 THEN engine_size = "Medium"',
            'condition' => fn(array $facts): bool => isset($facts['engine_cc']) && $facts['engine_cc'] > 1000 && $facts['engine_cc'] <= 1600 && !isset($facts['engine_size']),
            'action' => function (array &$facts): void {
                $facts['engine_size'] = 'Medium';
            },
        ],
        [
            'id' => 'R5',
            'description' => 'IF 1600 < engine_cc <= 2500 THEN engine_size = "Large"',
            'condition' => fn(array $facts): bool => isset($facts['engine_cc']) && $facts['engine_cc'] > 1600 && $facts['engine_cc'] <= 2500 && !isset($facts['engine_size']),
            'action' => function (array &$facts): void {
                $facts['engine_size'] = 'Large';
            },
        ],
        [
            'id' => 'R6',
            'description' => 'IF engine_cc > 2500 THEN engine_size = "Very Large"',
            'condition' => fn(array $facts): bool => isset($facts['engine_cc']) && $facts['engine_cc'] > 2500 && !isset($facts['engine_size']),
            'action' => function (array &$facts): void {
                $facts['engine_size'] = 'Very Large';
            },
        ],

        // --- Derive fuel_economy from km_per_liter ---
        [
            'id' => 'R7',
            'description' => 'IF km_per_liter >= 20 THEN fuel_economy = "Very Economical"',
            'condition' => fn(array $facts): bool =>
                isset($facts['km_per_liter']) &&
                $facts['km_per_liter'] >= 20 &&
                !isset($facts['fuel_economy']),
            'action' => function (array &$facts): void {
                $facts['fuel_economy'] = 'Very Economical';
            },
        ],
        [
            'id' => 'R8',
            'description' => 'IF 15 <= km_per_liter < 20 THEN fuel_economy = "Economical"',
            'condition' => fn(array $facts): bool =>
                isset($facts['km_per_liter']) &&
                $facts['km_per_liter'] >= 15 &&
                $facts['km_per_liter'] < 20 &&
                !isset($facts['fuel_economy']),
            'action' => function (array &$facts): void {
                $facts['fuel_economy'] = 'Economical';
            },
        ],
        [
            'id' => 'R9',
            'description' => 'IF 10 <= km_per_liter < 15 THEN fuel_economy = "Moderate"',
            'condition' => fn(array $facts): bool =>
                isset($facts['km_per_liter']) &&
                $facts['km_per_liter'] >= 10 &&
                $facts['km_per_liter'] < 15 &&
                !isset($facts['fuel_economy']),
            'action' => function (array &$facts): void {
                $facts['fuel_economy'] = 'Moderate';
            },
        ],
        [
            'id' => 'R10',
            'description' => 'IF km_per_liter < 10 THEN fuel_economy = "Thirsty"',
            'condition' => fn(array $facts): bool =>
                isset($facts['km_per_liter']) &&
                $facts['km_per_liter'] < 10 &&
                !isset($facts['fuel_economy']),
            'action' => function (array &$facts): void {
                $facts['fuel_economy'] = 'Thirsty';
            },
        ],

        // --- Derive recommended_use from previously inferred facts (true chaining) ---
        [
            'id' => 'R11',
            'description' => 'IF body_type = "MPV" THEN recommended_use = "Family Use"',
            'condition' => fn(array $facts): bool => ($facts['body_type'] ?? null) === 'mpv' && !isset($facts['recommended_use']),
            'action' => function (array &$facts): void {
                $facts['recommended_use'] = 'Family Use';
            },
        ],
        [
            'id' => 'R12',
            'description' => 'IF engine_size = "Electric" THEN recommended_use = "Urban EV / Eco-Friendly Commuting"',
            'condition' => fn(array $facts): bool => ($facts['engine_size'] ?? null) === 'Electric' && !isset($facts['recommended_use']),
            'action' => function (array &$facts): void {
                $facts['recommended_use'] = 'Urban EV / Eco-Friendly Commuting';
            },
        ],
        [
            'id' => 'R13',
            'description' => 'IF fuel_economy is "Very Economical" or "Economical" AND engine_size is "Small" or "Medium" THEN recommended_use = "City / Daily Commuting"',
            'condition' => fn(array $facts): bool => in_array($facts['fuel_economy'] ?? null, ['Very Economical', 'Economical'], true)
                && in_array($facts['engine_size'] ?? null, ['Small', 'Medium'], true)
                && !isset($facts['recommended_use']),
            'action' => function (array &$facts): void {
                $facts['recommended_use'] = 'City / Daily Commuting';
            },
        ],
        [
            'id' => 'R14',
            'description' => 'IF max_distance >= 600 AND fuel_economy is "Very Economical" or "Economical" THEN recommended_use = "Long-Distance / Highway Touring"',
            'condition' => fn(array $facts): bool => ($facts['max_distance'] ?? 0) >= 600
                && in_array($facts['fuel_economy'] ?? null, ['Very Economical', 'Economical'], true)
                && !isset($facts['recommended_use']),
            'action' => function (array &$facts): void {
                $facts['recommended_use'] = 'Long-Distance / Highway Touring';
            },
        ],
        [
            'id' => 'R15',
            'description' => 'IF engine_size is "Large" or "Very Large" AND fuel_economy = "Thirsty" THEN recommended_use = "Performance / Power-Oriented Driving"',
            'condition' => fn(array $facts): bool => in_array($facts['engine_size'] ?? null, ['Large', 'Very Large'], true)
                && ($facts['fuel_economy'] ?? null) === 'Thirsty'
                && !isset($facts['recommended_use']),
            'action' => function (array &$facts): void {
                $facts['recommended_use'] = 'Performance / Power-Oriented Driving';
            },
        ],
        [
            'id' => 'R16',
            'description' => 'IF engine_size AND fuel_economy are known AND no other recommended_use rule fired THEN recommended_use = "General Purpose / Family Use"',
            'condition' => fn(array $facts): bool => isset($facts['engine_size'], $facts['fuel_economy']) && !isset($facts['recommended_use']),
            'action' => function (array &$facts): void {
                $facts['recommended_use'] = 'General Purpose / Family Use';
            },
        ],
    ];
}

/**
 * Forward-chaining inference engine: repeatedly scans the knowledge base and
 * fires any rule whose condition is met, adding its conclusion to the fact
 * base, until a full pass produces no new facts (fixpoint).
 */
function runForwardChaining(array $facts, array $rules): array
{
    $trace = [];
    $changed = true;

    while ($changed) {
        $changed = false;
        foreach ($rules as $rule) {
            if (($rule['condition'])($facts)) {
                ($rule['action'])($facts);
                $trace[] = [
                    'rule' => $rule['id'],
                    'description' => $rule['description'],
                    'facts_after' => $facts,
                ];
                $changed = true;
            }
        }
    }

    return ['facts' => $facts, 'trace' => $trace];
}

/**
 * Runs the fuel-efficiency knowledge base against a car's known values and
 * returns the fully inferred facts plus the rule-firing trace.
 */
function inferCarUsageProfile(float $km_per_liter, float $full_tank_liter, ?float $engine_cc): array
{
    $initialFacts = [
        'km_per_liter' => $km_per_liter,
        'full_tank_liter' => $full_tank_liter,
        'engine_cc' => $engine_cc,
    ];

    return runForwardChaining($initialFacts, getFuelKnowledgeBaseRules());
}

/**
 * Example run using sample input values (18 km/L, 45L tank, 1500cc engine).
 * Returns the same shape as inferCarUsageProfile(): ['facts' => ..., 'trace' => ...].
 */
function getSampleForwardChainingExample(): array
{
    return inferCarUsageProfile(18.0, 45.0, 1500.0);
}

<?php
/**
 * top_cars.php
 * DriveSmart – Malaysia's Top 10 Most Registered Cars (year-to-date)
 *
 * JPJ's official car registration transactions dataset on data.gov.my has
 * no OpenAPI access ("not available through OpenAPI as the nature of the
 * data makes it unsuitable for API access") — only a bulk per-year CSV/
 * parquet download at storage.data.gov.my/transportation/cars_{year}.csv.
 *
 * This endpoint downloads that year's CSV, tallies registrations by
 * maker+model, and caches the resulting top-10 leaderboard on disk so the
 * ~20MB+ file is only re-downloaded and re-counted once per TOP_CARS_CACHE_TTL,
 * not on every page view.
 *
 * Returns JSON: { success, year, generated_at, total_registrations, cars, error }
 */

header('Content-Type: application/json');
set_time_limit(120);

define('TOP_CARS_CACHE_DIR', __DIR__ . '/cache/top_cars');
define('TOP_CARS_CACHE_TTL', 60 * 60 * 24); // 24 hours

function respond_error(string $message, int $status = 502): void
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function read_cache(string $path, int $ttl): ?array
{
    if (!is_file($path)) {
        return null;
    }

    $raw = @file_get_contents($path);
    if ($raw === false) {
        return null;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || !isset($decoded['cached_at'])) {
        return null;
    }

    if ((time() - (int) $decoded['cached_at']) > $ttl) {
        return null; // expired
    }

    return $decoded;
}

function write_cache(string $path, array $payload): void
{
    $payload['cached_at'] = time();
    $encoded = json_encode($payload);
    if ($encoded !== false) {
        @file_put_contents($path, $encoded, LOCK_EX);
    }
}

/**
 * Download a year's registration CSV (streamed to a temp file, then read
 * back row-by-row) and tally the top 10 maker+model combinations. Memory
 * use stays flat regardless of file size since rows are never all held
 * in memory at once — only the running maker+model counts are.
 */
function build_top_cars(int $year): ?array
{
    $url = "https://storage.data.gov.my/transportation/cars_{$year}.csv";
    $tmpFile = tempnam(sys_get_temp_dir(), 'jpj_cars_');
    if ($tmpFile === false) {
        return null;
    }

    $fp = fopen($tmpFile, 'w+b');
    if ($fp === false) {
        @unlink($tmpFile);
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE           => $fp,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_HTTPHEADER     => ['Accept: text/csv'],
    ]);
    $ok = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if (!$ok || $httpCode !== 200) {
        @unlink($tmpFile);
        return null;
    }

    $handle = fopen($tmpFile, 'r');
    if ($handle === false) {
        @unlink($tmpFile);
        return null;
    }

    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        @unlink($tmpFile);
        return null;
    }

    $makerIdx = array_search('maker', $header, true);
    $modelIdx = array_search('model', $header, true);

    if ($makerIdx === false || $modelIdx === false) {
        fclose($handle);
        @unlink($tmpFile);
        return null;
    }

    $counts = [];
    $total = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (!isset($row[$makerIdx], $row[$modelIdx])) {
            continue;
        }
        $maker = trim($row[$makerIdx]);
        $model = trim($row[$modelIdx]);
        if ($maker === '' || $model === '') {
            continue;
        }
        $key = $maker . '|' . $model;
        $counts[$key] = ($counts[$key] ?? 0) + 1;
        $total++;
    }
    fclose($handle);
    @unlink($tmpFile);

    if ($total === 0) {
        return null;
    }

    arsort($counts);
    $top = array_slice($counts, 0, 10, true);

    $cars = [];
    $rank = 1;
    foreach ($top as $key => $count) {
        [$maker, $model] = explode('|', $key, 2);
        $cars[] = [
            'rank'  => $rank++,
            'maker' => $maker,
            'model' => $model,
            'count' => $count,
        ];
    }

    return [
        'year'                => $year,
        'total_registrations' => $total,
        'cars'                => $cars,
    ];
}

if (!is_dir(TOP_CARS_CACHE_DIR)) {
    @mkdir(TOP_CARS_CACHE_DIR, 0775, true);
}

$year = (int) date('Y');
$cachePath = TOP_CARS_CACHE_DIR . "/top_cars_{$year}.json";

$cached = read_cache($cachePath, TOP_CARS_CACHE_TTL);
if ($cached !== null) {
    echo json_encode([
        'success'             => true,
        'year'                => $cached['year'],
        'generated_at'        => date('c', (int) $cached['cached_at']),
        'total_registrations' => $cached['total_registrations'],
        'cars'                => $cached['cars'],
        'from_cache'          => true,
    ]);
    exit;
}

$result = build_top_cars($year);

// Fall back to last year's file if this year's isn't published yet
// (e.g. very early January) or the download failed for any reason.
if ($result === null) {
    $fallbackYear = $year - 1;
    $fallbackResult = build_top_cars($fallbackYear);
    if ($fallbackResult !== null) {
        $year = $fallbackYear;
        $cachePath = TOP_CARS_CACHE_DIR . "/top_cars_{$year}.json";
        $result = $fallbackResult;
    }
}

if ($result === null) {
    respond_error('Could not build the top registered cars list right now.');
}

write_cache($cachePath, $result);

echo json_encode([
    'success'             => true,
    'year'                => $result['year'],
    'generated_at'        => date('c'),
    'total_registrations' => $result['total_registrations'],
    'cars'                => $result['cars'],
    'from_cache'          => false,
]);

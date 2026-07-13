<?php
/**
 * fuel_price.php
 * DriveSmart – Malaysia retail fuel price endpoint
 *
 * Proxies the official weekly fuel price series published by data.gov.my
 * (the same dataset backing https://storage.data.gov.my/commodities/fuelprice.parquet)
 * and returns just the latest RON95 / RON97 / Diesel prices plus the
 * change from the previous week.
 *
 * Returns JSON: { success, date, prices: {ron95, ron97, diesel}, change, error }
 */

header('Content-Type: application/json');

function respond_error(string $message, int $status = 502): void
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// Ask for the latest few rows only (both "level" and "change_weekly" series
// share the same most recent date), newest first.
$apiUrl = 'https://api.data.gov.my/data-catalogue?' . http_build_query([
    'id'    => 'fuelprice',
    'limit' => 4,
    'sort'  => '-date',
]);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 3,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    respond_error('Unable to reach the fuel price service: ' . $curlError);
}

if ($httpCode !== 200) {
    respond_error('Fuel price service returned HTTP ' . $httpCode);
}

$rows = json_decode($response, true);
if (!is_array($rows)) {
    respond_error('Unexpected response from the fuel price service.');
}

$level = null;
$change = null;
foreach ($rows as $row) {
    $seriesType = $row['series_type'] ?? '';
    if ($seriesType === 'level' && $level === null) {
        $level = $row;
    } elseif ($seriesType === 'change_weekly' && $change === null) {
        $change = $row;
    }
    if ($level !== null && $change !== null) {
        break;
    }
}

if ($level === null) {
    respond_error('No fuel price data is currently available.');
}

echo json_encode([
    'success' => true,
    'date'    => $level['date'],
    'prices'  => [
        'ron95'  => (float) $level['ron95'],
        'ron97'  => (float) $level['ron97'],
        'diesel' => (float) $level['diesel'],
    ],
    'change' => $change === null ? null : [
        'ron95'  => (float) $change['ron95'],
        'ron97'  => (float) $change['ron97'],
        'diesel' => (float) $change['diesel'],
    ],
]);

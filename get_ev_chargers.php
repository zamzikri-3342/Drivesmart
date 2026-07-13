<?php
/**
 * get_ev_chargers.php
 * DriveSmart – EV Charging Station lookup endpoint
 *
 * Accepts GET: lat, lon (required)
 * Returns JSON: { success, radius_km, message, stations[], count, error }
 *
 * Example: get_ev_chargers.php?lat=3.1390&lon=101.6869
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/OpenChargeMapService.php';

// ── Input validation ─────────────────────────────────────────────────────
$lat = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float) $_GET['lat'] : null;
$lon = isset($_GET['lon']) && is_numeric($_GET['lon']) ? (float) $_GET['lon'] : null;

if ($lat === null || $lon === null || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    http_response_code(400);
    echo json_encode([
        'success'  => false,
        'error'    => 'Valid "lat" and "lon" query parameters are required.',
        'stations' => [],
    ]);
    exit;
}

$defaultRadius = defined('OCM_DEFAULT_RADIUS_KM') ? (float) OCM_DEFAULT_RADIUS_KM : 5.0;
$maxRadius     = defined('OCM_MAX_RADIUS_KM') ? (float) OCM_MAX_RADIUS_KM : 10.0;

try {
    $service = new OpenChargeMapService();
    $result  = $service->findNearbyStations($lat, $lon, $defaultRadius);
} catch (Throwable $e) {
    // Catch-all so the Finance page never breaks even on unexpected failures.
    echo json_encode([
        'success'  => false,
        'error'    => 'Unable to reach the EV charging station service right now.',
        'stations' => [],
    ]);
    exit;
}

// API failure (network error, invalid response, bad key, etc.) — fail gracefully.
if (!$result['success']) {
    echo json_encode([
        'success'  => false,
        'error'    => $result['error'] ?? 'Unable to fetch EV charging stations at this time.',
        'stations' => [],
    ]);
    exit;
}

$stations   = $result['stations'];
$radiusUsed = $defaultRadius;
$message    = null;

// ── Fallback: widen the search radius if nothing was found nearby ─────────
if (empty($stations) && $maxRadius > $defaultRadius) {
    try {
        $fallback = $service->findNearbyStations($lat, $lon, $maxRadius);
    } catch (Throwable $e) {
        $fallback = ['success' => false, 'stations' => []];
    }

    if ($fallback['success']) {
        $stations   = $fallback['stations'];
        $radiusUsed = $maxRadius;

        $message = !empty($stations)
            ? "Limited EV charging infrastructure was found within {$defaultRadius} km. Showing charging stations within {$maxRadius} km."
            : "No EV charging stations were found within {$maxRadius} km of your home.";
    }
}

echo json_encode([
    'success'   => true,
    'radius_km' => $radiusUsed,
    'message'   => $message,
    'stations'  => $stations,
    'count'     => count($stations),
]);

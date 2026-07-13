<?php
/**
 * travel_info.php
 * DriveSmart – Travel Information backend
 * Accepts POST: home_address, workplace_address, working_days
 * Returns JSON: { success, one_way_km, round_trip_km, monthly_km, travel_time_minutes, error }
 *
 * Uses:
 *  - Nominatim (OpenStreetMap) for geocoding  — free, no API key required
 *  - OSRM public API for driving directions   — free, no API key required
 */

header('Content-Type: application/json');

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Geocode an address string via Nominatim.
 * Returns ['lat' => float, 'lng' => float] or null on failure.
 */
function nominatim_geocode(string $address): ?array
{
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q'              => $address,
        'format'         => 'json',
        'limit'          => 1,
        'addressdetails' => 0,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            // Nominatim requires a descriptive User-Agent
            'User-Agent: DriveSmart/1.0 (student project)',
            'Accept-Language: en',
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data) || !isset($data[0]['lat'], $data[0]['lon'])) {
        return null;
    }

    return [
        'lat' => (float) $data[0]['lat'],
        'lng' => (float) $data[0]['lon'],
    ];
}

/**
 * Get driving route between two coordinate pairs via OSRM public API.
 * Returns ['distance_m' => float, 'duration_s' => float] or null on failure.
 */
function osrm_directions(array $from, array $to): ?array
{
    // OSRM coordinate order: longitude,latitude
    $url = sprintf(
        'https://router.project-osrm.org/route/v1/driving/%f,%f;%f,%f?overview=false',
        $from['lng'], $from['lat'],
        $to['lng'],   $to['lat']
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data['routes'][0])) {
        return null;
    }

    return [
        'distance_m' => (float) $data['routes'][0]['distance'],  // metres
        'duration_s' => (float) $data['routes'][0]['duration'],  // seconds
    ];
}

// ── Main ─────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$homeAddress = trim($_POST['home_address']      ?? '');
$workAddress = trim($_POST['workplace_address'] ?? '');
$workingDays = (int) ($_POST['working_days']    ?? 0);

// Basic input validation
if ($homeAddress === '' || $workAddress === '') {
    echo json_encode(['success' => false, 'error' => 'Both addresses are required.']);
    exit;
}
if ($workingDays < 1 || $workingDays > 31) {
    echo json_encode(['success' => false, 'error' => 'Working days must be between 1 and 31.']);
    exit;
}

// Geocode home address
$homeCoords = nominatim_geocode($homeAddress);
if ($homeCoords === null) {
    echo json_encode(['success' => false, 'error' => 'Could not find your home address. Try adding more detail, e.g. "Ampang, Kuala Lumpur, Malaysia".']);
    exit;
}

// Nominatim rate-limit: 1 request/second
sleep(1);

// Geocode workplace address
$workCoords = nominatim_geocode($workAddress);
if ($workCoords === null) {
    echo json_encode(['success' => false, 'error' => 'Could not find your workplace address. Try adding more detail, e.g. "KLCC, Kuala Lumpur, Malaysia".']);
    exit;
}

// Get driving route via OSRM
$route = osrm_directions($homeCoords, $workCoords);
if ($route === null) {
    echo json_encode(['success' => false, 'error' => 'Could not calculate a driving route between those addresses. Please verify both are reachable by road.']);
    exit;
}

$oneWayKm    = round($route['distance_m'] / 1000, 1);
$roundTripKm = round($oneWayKm * 2, 1);
$monthlyKm   = round($roundTripKm * $workingDays, 1);
$travelMins  = (int) round($route['duration_s'] / 60);

echo json_encode([
    'success'             => true,
    'one_way_km'          => $oneWayKm,
    'round_trip_km'       => $roundTripKm,
    'monthly_km'          => $monthlyKm,
    'travel_time_minutes' => $travelMins,
    // Home coordinates are returned so the front-end can automatically
    // look up nearby EV charging stations (see get_ev_chargers.php).
    'home_lat'            => $homeCoords['lat'],
    'home_lng'            => $homeCoords['lng'],
]);
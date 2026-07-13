<?php
/**
 * geocode.php
 * DriveSmart – Standalone address geocoding endpoint
 *
 * Used by the "Find EV Charger Station" tool on cars.php, so a user can type
 * any location (not just their home/workplace commute addresses) and get
 * back coordinates to feed into get_ev_chargers.php.
 *
 * Accepts GET or POST: address
 * Returns JSON: { success, lat, lng, error }
 */

header('Content-Type: application/json');

/**
 * Geocode an address string via Nominatim (OpenStreetMap).
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

$address = trim($_REQUEST['address'] ?? '');

if ($address === '') {
    echo json_encode(['success' => false, 'error' => 'Please enter a location to search.']);
    exit;
}

$coords = nominatim_geocode($address);

if ($coords === null) {
    echo json_encode([
        'success' => false,
        'error'   => 'Could not find that location. Try adding more detail, e.g. "Shah Alam, Selangor, Malaysia".',
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'lat'     => $coords['lat'],
    'lng'     => $coords['lng'],
]);

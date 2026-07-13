<?php
/**
 * get_timezone.php
 * DriveSmart – World clock lookup endpoint (TimeZoneDB)
 *
 * Accepts GET: zone (IANA time zone name, e.g. "Asia/Kuala_Lumpur")
 * Returns JSON: { success, source, zoneName, countryName, gmtOffset, dst, formatted, timestamp, error }
 *
 * Uses the TimeZoneDB API (https://timezonedb.com) when TIMEZONEDB_API_KEY is
 * configured in config.php. Falls back to PHP's built-in time zone database
 * whenever the key is missing or the API is unreachable, so the widget never
 * breaks the pages it's embedded in.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

$zone = trim($_GET['zone'] ?? '');

if ($zone === '' || !in_array($zone, timezone_identifiers_list(), true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'A valid "zone" query parameter is required.']);
    exit;
}

$result = fetch_from_timezonedb($zone) ?? local_timezone_fallback($zone);

echo json_encode($result);

/**
 * Query TimeZoneDB for the current time in a zone.
 * Returns the response array on success, or null so the caller can fall back.
 */
function fetch_from_timezonedb(string $zone): ?array
{
    if (!defined('TIMEZONEDB_API_KEY') || TIMEZONEDB_API_KEY === '' || TIMEZONEDB_API_KEY === 'YOUR_TIMEZONEDB_API_KEY') {
        return null;
    }

    $url = 'https://api.timezonedb.com/v2.1/get-time-zone?' . http_build_query([
        'key'    => TIMEZONEDB_API_KEY,
        'format' => 'json',
        'by'     => 'zone',
        'zone'   => $zone,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data) || ($data['status'] ?? '') !== 'OK') {
        return null;
    }

    return [
        'success'     => true,
        'source'      => 'timezonedb',
        'zoneName'    => $data['zoneName'] ?? $zone,
        'countryName' => $data['countryName'] ?? '',
        'gmtOffset'   => (int) ($data['gmtOffset'] ?? 0),
        'dst'         => !empty($data['dst']) && $data['dst'] !== '0',
        'formatted'   => $data['formatted'] ?? '',
        'timestamp'   => (int) ($data['timestamp'] ?? time()),
    ];
}

/**
 * Compute the same response shape from PHP's own time zone database.
 * Used whenever TimeZoneDB is unreachable or no API key has been set yet.
 */
function local_timezone_fallback(string $zone): array
{
    $tz  = new DateTimeZone($zone);
    $now = new DateTime('now', $tz);

    return [
        'success'     => true,
        'source'      => 'local',
        'zoneName'    => $zone,
        'countryName' => '',
        'gmtOffset'   => $tz->getOffset($now),
        'dst'         => (bool) $now->format('I'),
        'formatted'   => $now->format('Y-m-d H:i:s'),
        'timestamp'   => $now->getTimestamp(),
    ];
}

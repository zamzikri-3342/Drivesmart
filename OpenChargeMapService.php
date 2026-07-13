<?php
/**
 * OpenChargeMapService.php
 * DriveSmart – Open Charge Map integration
 *
 * Reusable service class responsible for:
 *  - Calling the Open Charge Map API (via PHP cURL)
 *  - Searching nearby EV charging stations by lat/lng + radius
 *  - Parsing the raw API response into a clean, structured array
 *  - Caching responses on disk for OCM_CACHE_TTL seconds (default 24h)
 *
 * Usage:
 *   require_once 'config.php';
 *   require_once 'OpenChargeMapService.php';
 *   $service = new OpenChargeMapService();
 *   $result  = $service->findNearbyStations(3.1390, 101.6869, 5);
 */

class OpenChargeMapService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openchargemap.io/v3/poi/';
    private string $cacheDir;
    private int $cacheTtl;

    public function __construct(?string $apiKey = null, ?string $cacheDir = null, ?int $cacheTtl = null)
    {
        $this->apiKey   = $apiKey   ?? (defined('OCM_API_KEY') ? OCM_API_KEY : (getenv('OCM_API_KEY') ?: ''));
        $this->cacheDir = $cacheDir ?? (defined('OCM_CACHE_DIR') ? OCM_CACHE_DIR : __DIR__ . '/cache/ocm');
        $this->cacheTtl = $cacheTtl ?? (defined('OCM_CACHE_TTL') ? OCM_CACHE_TTL : 86400);

        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }
    }

    /**
     * Find EV charging stations near a coordinate, within a given radius (km).
     *
     * @return array{success: bool, stations: array, error: ?string, from_cache: bool}
     */
    public function findNearbyStations(float $lat, float $lon, float $radiusKm, int $maxResults = 25): array
    {
        $cacheKey = $this->buildCacheKey($lat, $lon, $radiusKm, $maxResults);
        $cached   = $this->readCache($cacheKey);

        if ($cached !== null) {
            return ['success' => true, 'stations' => $cached, 'error' => null, 'from_cache' => true];
        }

        if ($this->apiKey === '' || $this->apiKey === 'PUT_YOUR_OPENCHARGEMAP_API_KEY_HERE') {
            return [
                'success'    => false,
                'stations'   => [],
                'error'      => 'Open Charge Map API key is not configured. Set OCM_API_KEY in config.php or your .env file.',
                'from_cache' => false,
            ];
        }

        $url = $this->baseUrl . '?' . http_build_query([
            'output'       => 'json',
            'latitude'     => $lat,
            'longitude'    => $lon,
            'distance'     => $radiusKm,
            'distanceunit' => 'KM',
            'maxresults'   => $maxResults,
            'compact'      => 'true',
            'verbose'      => 'false',
            'key'          => $this->apiKey,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'User-Agent: DriveSmart/1.0 (EV charger lookup)',
            ],
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Network error / timeout
        if ($response === false || $curlErrno !== 0) {
            return [
                'success'    => false,
                'stations'   => [],
                'error'      => 'Network error contacting Open Charge Map' . ($curlError ? ": {$curlError}" : '.'),
                'from_cache' => false,
            ];
        }

        // Non-200 HTTP response
        if ($httpCode !== 200) {
            return [
                'success'    => false,
                'stations'   => [],
                'error'      => "Open Charge Map returned an unexpected HTTP status ({$httpCode}).",
                'from_cache' => false,
            ];
        }

        $data = json_decode($response, true);

        // Invalid / unparsable JSON
        if (!is_array($data)) {
            return [
                'success'    => false,
                'stations'   => [],
                'error'      => 'Received an invalid response from Open Charge Map.',
                'from_cache' => false,
            ];
        }

        $stations = $this->parseStations($data);

        // Cache even empty results, so we don't hammer the API for "no stations here" areas.
        $this->writeCache($cacheKey, $stations);

        return ['success' => true, 'stations' => $stations, 'error' => null, 'from_cache' => false];
    }

    /**
     * Convert raw Open Charge Map POI records into a clean, front-end friendly array.
     */
    private function parseStations(array $data): array
    {
        $stations = [];

        foreach ($data as $poi) {
            $addressInfo = $poi['AddressInfo'] ?? null;
            if (!$addressInfo) {
                continue;
            }

            $addressParts = array_filter([
                $addressInfo['AddressLine1']     ?? null,
                $addressInfo['Town']             ?? null,
                $addressInfo['StateOrProvince']  ?? null,
                $addressInfo['Postcode']         ?? null,
            ]);

            $connections      = $poi['Connections'] ?? [];
            $connectionTypes  = [];
            $totalPoints      = 0;
            $hasFastCharger   = false;

            foreach ($connections as $conn) {
                $typeTitle = $conn['ConnectionType']['Title'] ?? null;
                if ($typeTitle) {
                    $connectionTypes[] = $typeTitle;
                }

                $levelTitle = $conn['Level']['Title'] ?? '';
                $powerKw    = $conn['PowerKW'] ?? null;

                if ((is_numeric($powerKw) && $powerKw >= 22)
                    || stripos($levelTitle, 'fast') !== false
                    || stripos($levelTitle, 'rapid') !== false) {
                    $hasFastCharger = true;
                }

                $quantity = $conn['Quantity'] ?? 1;
                $totalPoints += (int) ($quantity ?: 1);
            }

            $connectionTypes = array_values(array_unique(array_filter($connectionTypes)));

            $chargerType = null;
            if (!empty($connectionTypes)) {
                if ($hasFastCharger) {
                    $chargerType = (count($connectionTypes) > 1) ? 'AC + DC Fast Charger' : 'DC Fast Charger';
                } else {
                    $chargerType = 'Standard / AC Charger';
                }
            }

            $stations[] = [
                'id'               => $poi['ID'] ?? null,
                'name'             => $addressInfo['Title'] ?? 'Unnamed Charging Station',
                'address'          => $addressParts ? implode(', ', $addressParts) : 'Address not available',
                'distance_km'      => isset($addressInfo['Distance']) ? round((float) $addressInfo['Distance'], 1) : null,
                'operator'         => $poi['OperatorInfo']['Title'] ?? null,
                'charger_type'     => $chargerType,
                'connection_types' => $connectionTypes,
                'num_points'       => $totalPoints > 0 ? $totalPoints : null,
                'latitude'         => $addressInfo['Latitude']  ?? null,
                'longitude'        => $addressInfo['Longitude'] ?? null,
            ];
        }

        // Sort by nearest distance first (unknown distances go last).
        usort($stations, function ($a, $b) {
            $distanceA = $a['distance_km'] ?? PHP_FLOAT_MAX;
            $distanceB = $b['distance_km'] ?? PHP_FLOAT_MAX;
            return $distanceA <=> $distanceB;
        });

        return $stations;
    }

    private function buildCacheKey(float $lat, float $lon, float $radiusKm, int $maxResults): string
    {
        // Round coordinates to ~110m precision so nearby repeated searches reuse the same cache entry.
        $roundedLat = round($lat, 3);
        $roundedLon = round($lon, 3);
        return 'ocm_' . md5("{$roundedLat}_{$roundedLon}_{$radiusKm}_{$maxResults}") . '.json';
    }

    private function readCache(string $key): ?array
    {
        $path = rtrim($this->cacheDir, '/') . '/' . $key;

        if (!is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['cached_at'], $decoded['stations'])) {
            return null;
        }

        if ((time() - (int) $decoded['cached_at']) > $this->cacheTtl) {
            return null; // expired
        }

        return $decoded['stations'];
    }

    private function writeCache(string $key, array $stations): void
    {
        $path    = rtrim($this->cacheDir, '/') . '/' . $key;
        $payload = json_encode(['cached_at' => time(), 'stations' => $stations]);

        if ($payload !== false) {
            @file_put_contents($path, $payload, LOCK_EX);
        }
    }
}

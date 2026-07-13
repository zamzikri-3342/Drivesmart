<?php
// Load variables from a local .env file (never committed) into getenv(),
// so real secrets stay out of source-controlled files.
if (!defined('ENV_LOADED')) {
    define('ENV_LOADED', true);
    $envFile = __DIR__ . '/.env';
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'car_recommendation');
define('DB_USER', 'root');
define('DB_PASS', '');

// OpenRouteService API key — get yours free at https://openrouteservice.org/
define('ORS_API_KEY', 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6ImI3N2IzMjM5YjllYjQ0NzRhMWE3MjUxNDlhY2ViZDhjIiwiaCI6Im11cm11cjY0In0');

// ── Open Charge Map (EV Charging Stations) ─────────────────────────────────
// Get a free API key at https://openchargemap.org/site/develop/api
// You can also set these via a .env file / real environment variables
// (OCM_API_KEY, OCM_DEFAULT_RADIUS_KM, OCM_MAX_RADIUS_KM) — if present,
// the environment values take priority over the fallbacks below.
if (!defined('OCM_API_KEY')) {
    define('OCM_API_KEY', getenv('OCM_API_KEY') ?: '91ddf30d-2f57-4840-a0ee-e64713a3a308');
}
if (!defined('OCM_DEFAULT_RADIUS_KM')) {
    define('OCM_DEFAULT_RADIUS_KM', getenv('OCM_DEFAULT_RADIUS_KM') ?: 5);
}
if (!defined('OCM_MAX_RADIUS_KM')) {
    define('OCM_MAX_RADIUS_KM', getenv('OCM_MAX_RADIUS_KM') ?: 10);
}
// Where cached Open Charge Map responses are stored, and for how long (seconds).
if (!defined('OCM_CACHE_DIR')) {
    define('OCM_CACHE_DIR', __DIR__ . '/cache/ocm');
}
if (!defined('OCM_CACHE_TTL')) {
    define('OCM_CACHE_TTL', 60 * 60 * 24); // 24 hours
}
// unplash.com API key — get yours free at https://unsplash.com/developers
// define('UNSPLASH_ACCESS_KEY', 'ajrMbNIIwcjTALc9JUWsIr3ahj6k0RNOZyi-aDunHg0');

// ── TimeZoneDB (World Clock widget) ─────────────────────────────────────────
// Free API key, no credit card required — get yours at https://timezonedb.com/register
// Until a real key is set below, get_timezone.php automatically falls back to
// PHP's built-in time zone database so the clock widget still works.
if (!defined('TIMEZONEDB_API_KEY')) {
    define('TIMEZONEDB_API_KEY', getenv('TIMEZONEDB_API_KEY') ?: 'YOUR_TIMEZONEDB_API_KEY');
}

// ── hCaptcha (Admin Login) ──────────────────────────────────────────────────
// Site key is public and safe to hardcode. Secret key must stay in .env only.
if (!defined('HCAPTCHA_SITE_KEY')) {
    define('HCAPTCHA_SITE_KEY', 'aefe452a-2b72-479a-90fe-ec34b9363bb6');
}
if (!defined('HCAPTCHA_SECRET_KEY')) {
    define('HCAPTCHA_SECRET_KEY', getenv('HCAPTCHA_SECRET_KEY') ?: '');
}
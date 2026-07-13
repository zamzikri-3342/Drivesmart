<?php
/**
 * unsplash_proxy.php
 * DriveSmart – Unsplash image proxy
 *
 * Keeps the Unsplash API key server-side so it is never exposed in
 * the browser. Called by cars.php JavaScript with ?brand=&model=
 *
 * Returns JSON: { success, url, error }
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

$brand = trim($_GET['brand'] ?? '');
$model = trim($_GET['model'] ?? '');

if ($brand === '' || $model === '') {
    echo json_encode(['success' => false, 'error' => 'Missing brand or model.']);
    exit;
}

if (!defined('UNSPLASH_ACCESS_KEY') || UNSPLASH_ACCESS_KEY === 'your_actual_key_here') {
    echo json_encode(['success' => false, 'error' => 'Unsplash API key is not configured.']);
    exit;
}

$query = urlencode($brand . ' ' . $model . ' car');
$url   = "https://api.unsplash.com/search/photos?query={$query}&per_page=1&orientation=landscape";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Client-ID ' . UNSPLASH_ACCESS_KEY,
        'Accept-Version: v1',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo json_encode(['success' => false, 'error' => 'Network error contacting Unsplash.']);
    exit;
}

if ($httpCode === 401 || $httpCode === 403) {
    echo json_encode(['success' => false, 'error' => 'Invalid Unsplash API key.']);
    exit;
}

if ($httpCode === 429) {
    echo json_encode(['success' => false, 'error' => 'Unsplash rate limit reached (50/hr). Try again later.']);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'error' => "Unsplash returned HTTP {$httpCode}."]);
    exit;
}

$data    = json_decode($response, true);
$imageUrl = $data['results'][0]['urls']['regular'] ?? null;

if ($imageUrl === null) {
    echo json_encode(['success' => false, 'error' => 'No photo found for this car.']);
    exit;
}

echo json_encode(['success' => true, 'url' => $imageUrl]);

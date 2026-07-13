<?php
session_start();
require_once __DIR__ . '/functions.php';

/**
 * Convert a raw engine string to a human-readable display label.
 * - Pure CC values (e.g. "1290 CC", "1498 CC") are rounded to the
 *   nearest 0.1 L and shown as "1.3L", "1.5L", etc.
 * - Values already expressed in litres or labelled "No data" / "EV" are
 *   passed through unchanged.
 */
function formatEngineDisplay(string $engine): string
{
    $engine = trim($engine);

    // Already a litre value like "1.5L" or "2.0 L"
    if (preg_match('/^\d+(\.\d+)?\s*[Ll]$/', $engine)) {
        return $engine;
    }

    // Special cases – pass through as-is
    if (stripos($engine, 'ev') !== false
        || stripos($engine, 'electric') !== false
        || strtolower($engine) === 'no data') {
        return $engine;
    }

    // Numeric CC value (e.g. "1290 CC", "1498CC", or bare "1290")
    if (preg_match('/^([\d.]+)\s*(?:cc)?$/i', $engine, $m)) {
        $cc = (float) $m[1];
        if ($cc >= 100) {                           // sanity check – must look like CC
            $litres = number_format($cc / 1000, 1); // 1290 → 1.3, 1000 → 1.0, 2000 → 2.0
            return $litres . 'L';
        }
    }

    return $engine;   // fallback – return original
}

$maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : 0.0;
$netSalary = isset($_GET['gaji']) ? (float) $_GET['gaji'] : 0.0;
$deposit = isset($_GET['deposit']) ? (float) $_GET['deposit'] : 0.0;
$loanYears = isset($_GET['loanYears']) ? (int) $_GET['loanYears'] : 0;
$interest = isset($_GET['interest']) ? (float) $_GET['interest'] : 2.5;

if ($netSalary > 0 && $loanYears > 0) {
    $recommendation = calculateRecommendation($netSalary, $deposit, $loanYears, $interest);
    $maxPrice = isset($recommendation[0]) ? (float) $recommendation[0] : 0.0;
} elseif ($maxPrice <= 0) {
    $maxPrice = isset($_SESSION['max_price']) ? (float) $_SESSION['max_price'] : 0.0;
}

if ($maxPrice > 0) {
    $_SESSION['max_price'] = $maxPrice;
}

// Define all available body choices
$bodyOptions = ['sedan', 'hatchback', 'suv', 'pickup', 'mpv'];

// FIX: If the user hasn't clicked "Apply Filters" yet (URL doesn't have body selections), 
// default to showing ALL body types instead of filtering down.
$currentBodies = isset($_GET['body']) && is_array($_GET['body']) ? $_GET['body'] : $bodyOptions;
$currentEngines = isset($_GET['engine']) && is_array($_GET['engine']) ? $_GET['engine'] : [];
$currentBrands = isset($_GET['brand']) && is_array($_GET['brand']) ? $_GET['brand'] : [];
$currentSort = isset($_GET['sort']) && in_array($_GET['sort'], ['asc', 'desc'], true) ? $_GET['sort'] : 'asc';

// Use $currentBodies directly — it already defaults to all body types when no GET param is present
$sessionBodyTypes = $currentBodies;

$cars = loadCarData();

// Build the list of selectable brands straight from the loaded car data
$brandOptions = array_values(array_unique(array_map(fn($car) => $car['brand'], $cars)));
sort($brandOptions, SORT_STRING | SORT_FLAG_CASE);

$filteredCars = filterCars($cars, $maxPrice, $sessionBodyTypes, $currentEngines, $currentBodies, $currentBrands);
$filteredCars = sortCarsByPrice($filteredCars, $currentSort);

// Pull EV road tax values straight from the ev_cars table (keyed by car_id)
// so EV cards don't fall back to the CC-based calculation (which returns RM0 with no engine cc).
$evRoadTaxByCarId = [];
$dbConn = getDbConnection();
if ($dbConn !== null) {
    try {
        $evStmt = $dbConn->query('SELECT id, roadtax FROM ev_cars');
        foreach ($evStmt->fetchAll() as $evRow) {
            $evRoadTaxByCarId[(int) $evRow['id']] = (float) $evRow['roadtax'];
        }
    } catch (PDOException $e) {
        // ev_cars table not available/queryable; silently fall back to calculated road tax
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Perfect Car — Car Finder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="shared-styles.css">
    <script>
        // Preflight is disabled: this page already ships a complete hand-written
        // CSS reset/design system, and Tailwind is only added here for a handful
        // of layout utility classes on the shared nav bar.
        tailwind.config = {
            corePlugins: { preflight: false },
            theme: {
                extend: {
                    colors: {
                        brandGold: '#0d6efd',
                        brandGoldHover: '#0a54c7',
                        brandLuxuryDark: '#14171c',
                        brandSlate: '#6b7178',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Barlow Condensed', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --ink: #14171c;
            --paper: #ffffff;
            --mist: #f3f4f6;
            --line: #e3e5e9;
            --blue: #0d6efd;
            --blue-deep: #0a54c7;
            --good: #0a8f5b;
            --hybrid-tag: #7a4bd6;
            --petrol-tag: #5c6470;
            --warn: #b8860b;
            --danger: #c8102e;
            --muted: #6b7178;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--paper);
            color: var(--ink);
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, .display {
            font-family: 'Barlow Condensed', sans-serif;
            letter-spacing: .2px;
        }

        /* ---------- hero ---------- */
        .hero {
            max-width: 1280px;
            margin: 0 auto;
            padding: 48px 28px 8px;
        }

        .hero h1 {
            font-size: 44px;
            font-weight: 700;
            margin: 0 0 10px;
            line-height: 1.05;
        }

        .hero p {
            max-width: 620px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
        }

        /* ---------- layout: sidebar + content ---------- */
        .layout {
            max-width: 1280px;
            margin: 28px auto 0;
            padding: 0 28px 60px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 36px;
            align-items: start;
        }

        /* ---------- filter sidebar ---------- */
        .filterbar {
            position: sticky;
            top: 78px;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 20px 20px 20px;
            background: #fff;
            max-height: calc(100vh - 98px);
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .filterbar::-webkit-scrollbar {
            width: 6px;
        }

        .filterbar::-webkit-scrollbar-thumb {
            background: var(--line);
            border-radius: 999px;
        }

        .filter-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .filter-head h3 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .reset-link {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--blue);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }

        .filter-group {
            padding: 16px 0;
            border-bottom: 1px solid var(--line);
        }

        .filter-group:last-child {
            border-bottom: none;
        }

        .filter-group .label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .filter-options.stacked {
            flex-direction: column;
            gap: 2px;
        }

        .brand-scroll {
            max-height: 170px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .chip {
            border: 1px solid var(--line);
            background: none;
            color: var(--ink);
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s;
            text-align: left;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            user-select: none;
        }

        .filter-options.stacked .chip {
            width: 100%;
            justify-content: space-between;
            border-radius: 8px;
            border: none;
        }

        .chip input {
            display: none;
        }

        .chip:hover {
            background: var(--mist);
        }

        .chip.on {
            background: var(--ink);
            color: #fff;
            border-color: var(--ink);
        }

        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .field-label .value-pill {
            font-size: 11.5px;
            font-weight: 700;
            background: var(--blue);
            color: #fff;
            padding: 2px 9px;
            border-radius: 999px;
            font-variant-numeric: tabular-nums;
        }

        .input-wrap {
            display: flex;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--mist);
            overflow: hidden;
        }

        .input-wrap span {
            padding: 0 10px;
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
        }

        .input-wrap input {
            border: none;
            background: transparent;
            padding: 9px 8px 9px 0;
            font-size: 14px;
            width: 100%;
            outline: none;
            font-family: inherit;
        }

        input[type="range"] {
            width: 100%;
            accent-color: var(--blue);
        }

        select.select-field {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            font-size: 14px;
            font-family: inherit;
            color: var(--ink);
        }

        .ev-box {
            padding: 14px;
            border-radius: 10px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .ev-box p {
            margin: 0 0 3px;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--good);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .ev-box small {
            display: block;
            color: var(--muted);
            margin-bottom: 10px;
            font-size: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-align: center;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid var(--ink);
            font-family: inherit;
        }

        .btn.primary {
            background: var(--ink);
            color: #fff;
        }

        .btn.primary:hover {
            background: var(--blue-deep);
            border-color: var(--blue-deep);
        }

        .btn.ghost {
            background: #fff;
            color: var(--ink);
        }

        .btn.ghost:hover {
            background: var(--mist);
        }

        .btn.good {
            background: var(--good);
            border-color: var(--good);
            color: #fff;
        }

        .btn.good:hover {
            background: #067a4b;
        }

        .btn.block {
            width: 100%;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }

        .filter-actions .btn {
            flex: 1;
        }

        /* ---------- sections ---------- */
        .section-wrap {
            min-width: 0;
        }

        .section-topbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            border-bottom: 3px solid var(--ink);
            padding-bottom: 14px;
            margin-bottom: 24px;
        }

        .group-title {
            font-size: 30px;
            font-weight: 600;
            margin: 0;
        }

        .count-text {
            color: var(--muted);
            font-size: 13.5px;
            display: block;
            margin-top: 2px;
        }

        .budget-badge {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 14px;
            background: var(--good);
            color: #fff;
            padding: 11px 20px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 12px;
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .filterbar {
                position: static;
            }
        }

        /* ---------- card ---------- */
        .car-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s, transform .2s;
            cursor: pointer;
        }

        .car-card:hover {
            box-shadow: 0 10px 28px rgba(20, 23, 28, .10);
            transform: translateY(-2px);
        }

        /* While a card's popup is open, its .price-details is fixed-positioned but
           still a DOM descendant of .car-card — hovering the popup (even though it
           renders elsewhere on screen) still matches .car-card:hover. If that hover
           transform stayed active, this element would become the popup's fixed
           containing block, making it jitter/shift under the cursor. Neutralize it. */
        .car-card.expanded,
        .car-card.expanded:hover {
            transform: none;
            box-shadow: none;
            transition: none;
        }

        .car-card.border-highly-recommend {
            border: 2px solid var(--good);
        }

        .card-media {
            position: relative;
            aspect-ratio: 16/10;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(160deg, #f2f3f4 0%, #e6e8ea 55%, #dadde1 100%);
        }

        .card-media.media-electric {
            background: linear-gradient(160deg, #e7f3ff 0%, #cfe6ff 55%, #b7d8ff 100%);
        }

        .card-media.media-hybrid {
            background: linear-gradient(160deg, #f1ecff 0%, #e2d7ff 55%, #d2c4fb 100%);
        }

        .card-media.media-petrol {
            background: linear-gradient(160deg, #f2f3f4 0%, #e6e8ea 55%, #dadde1 100%);
        }

        .card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-media .placeholder-icon {
            font-size: 3.2rem;
            color: rgba(20, 23, 28, .3);
        }

        .badges-row {
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            pointer-events: none;
        }

        .tag {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 4px 9px;
            border-radius: 5px;
            color: #fff;
            background: var(--ink);
            background: rgba(20, 23, 28, .75);
        }

        .sticker-recommend {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 62px;
            height: 62px;
            border-radius: 50%;
            background: var(--good);
            color: #fff;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 700;
            font-size: 0.62rem;
            line-height: 1.15;
            text-transform: uppercase;
            letter-spacing: .04em;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .18);
            z-index: 3;
        }

        .card-body {
            padding: 16px 18px 18px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .kicker-row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }

        .kicker {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            background: var(--mist);
            padding: 3px 8px;
            border-radius: 5px;
        }

        .model-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 25px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .drivetrain-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex: none;
        }

        .dot.electric {
            background: var(--good);
        }

        .dot.hybrid {
            background: var(--hybrid-tag);
        }

        .dot.petrol {
            background: var(--petrol-tag);
        }

        .drivetrain-row span {
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
        }

        .usage-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: -6px 0 12px;
            font-size: 12px;
            color: var(--muted);
        }

        .usage-row .usage-tag {
            font-weight: 700;
            color: var(--blue-deep);
        }

        .price-box {
            background: var(--mist);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 12.5px;
        }

        .price-box .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .price-box .row:last-of-type {
            margin-bottom: 0;
        }

        .price-box .row.total {
            padding-top: 8px;
            border-top: 1px dashed var(--line);
            margin-top: 2px;
        }

        .price-box .muted-label {
            color: var(--muted);
        }

        .price-box .val {
            font-weight: 600;
            color: var(--ink);
        }

        .price-box .val.total {
            font-weight: 700;
            color: var(--good);
        }

        .deposit-block {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed var(--line);
        }

        .deposit-suggestion {
            margin-top: 6px;
            padding: 6px 8px;
            border-radius: 6px;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            background: #fff;
            color: var(--muted);
        }

        .fine-print {
            display: block;
            text-align: center;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid var(--line);
            font-size: 10.5px;
            color: var(--muted);
            opacity: .8;
            font-style: italic;
        }

        .price-line {
            font-size: 13px;
            color: var(--ink);
            margin-bottom: 14px;
        }

        .price-line b {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 22px;
            font-weight: 700;
            display: block;
            color: var(--blue-deep);
        }

        .price-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }

        .price-toggle .toggle-icon {
            color: var(--muted);
            font-size: 13px;
        }

        /* Price/installment panel pops up centered over the page (like a modal)
           instead of expanding inline below the card. */
        .price-details {
            display: none;
        }

        .car-card.expanded .price-details {
            display: block;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            width: 92%;
            max-width: 420px;
            max-height: 85vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .15);
            border: 1px solid var(--line);
            padding: 18px 20px 20px;
            cursor: default;
        }

        .price-details-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--line);
        }

        .price-details-toolbar h5 {
            margin: 0;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--ink);
        }

        .price-details-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9998;
            background-color: rgba(20, 23, 28, .45);
            backdrop-filter: blur(4px);
        }

        .price-details-backdrop.show {
            display: block;
        }

        .installment-box .installment-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .card-actions {
            margin-top: auto;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .card-actions .btn {
            flex: 1;
        }

        .no-results {
            text-align: center;
            color: var(--muted);
            padding: 60px 0;
            font-size: 15px;
            border: 1px dashed var(--line);
            border-radius: 14px;
        }

        .no-results i {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 12px;
            color: var(--warn);
        }

        footer {
            margin-top: 60px;
            background: var(--ink);
            color: rgba(255, 255, 255, .7);
            padding: 32px 28px 22px;
        }

        .foot-inner {
            max-width: 1280px;
            margin: 0 auto;
            font-size: 13px;
        }

        /* ---------- shared modal styling ---------- */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background-color: rgba(20, 23, 28, .45);
            backdrop-filter: blur(4px);
        }

        .modal-overlay.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-box {
            background: #fff;
            border-radius: 14px;
            width: 92%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .15);
            border: 1px solid var(--line);
            overflow: hidden;
        }

        .modal-box.ev {
            max-width: 560px;
            max-height: 88vh;
            overflow-y: auto;
        }

        .modal-toolbar {
            padding: 16px 22px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--mist);
        }

        .modal-toolbar h5 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: var(--ink);
        }

        .modal-close {
            background: transparent;
            border: none;
            color: var(--muted);
            font-size: 1.2rem;
            cursor: pointer;
            line-height: 1;
        }

        .modal-close:hover {
            color: var(--danger);
        }

        .modal-body {
            padding: 22px;
        }

        .ev-input-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .ev-input-row input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        .ev-station-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 12px 14px;
        }

        .ev-station-card+.ev-station-card {
            margin-top: 10px;
        }

        .ev-station-card p {
            font-weight: 700;
            color: var(--ink);
            margin: 0 0 6px;
        }

        .ev-station-card ul {
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            font-size: 12.5px;
        }

        .ev-spinner {
            border: 3px solid var(--line);
            border-top-color: var(--good);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: ev-spin .7s linear infinite;
            display: inline-block;
        }

        @keyframes ev-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .text-error {
            color: var(--danger);
            font-size: 12.5px;
        }

        .box-error {
            color: var(--danger);
            background: #fdeaea;
            border: 1px solid #f5c2c2;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 12.5px;
        }

        .box-warning {
            color: var(--warn);
            background: #fff7e0;
            border: 1px solid #f0e0a8;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 12.5px;
        }

        .d-none {
            display: none !important;
        }

        .ev-loading {
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 13.5px;
            padding: 12px 0;
        }
    </style>
</head>

<body>

    <!-- Navigation Bar -->
    <header class="sticky top-0 z-50 bg-brandLuxuryDark border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                <!-- Logo/Title -->
                <span class="order-1 flex items-center gap-2.5">
                    <span class="brand-mark"></span>
                    <span class="brand-text">DriveSmart</span>
                </span>

                <!-- Page Navigation -->
                <nav class="order-3 sm:order-2 flex flex-wrap w-full sm:w-auto items-center justify-center gap-1">
                    <a href="index.html" class="nav-link">Home</a>
                    <a href="recommendation.php" class="nav-link active">Recommendations</a>
                    <a href="finance.php" class="nav-link">Finance Tools</a>
                </nav>

                <!-- Timezone Widget -->
                <div data-timezone-widget class="order-2 sm:order-3"></div>
            </div>
        </div>
    </header>

    <section class="hero">
        <h1>Find Your Perfect Car</h1>
        <p>Smart car recommendations matched flawlessly with your budget — filter by body type, engine, brand, and financing to see what fits.</p>
    </section>

    <div class="layout">
        <aside class="filterbar">
            <form action="cars.php" method="GET" id="filterForm">
                <div class="filter-head">
                    <h3>Filters</h3>
                    <button type="button" class="reset-link" id="clearFiltersBtn">Reset all</button>
                </div>

                <div class="filter-group">
                    <span class="label"><i class="bi bi-car-front"></i> Body Type</span>
                    <div class="filter-options">
                        <?php
                        $bodyOptions = ['sedan', 'hatchback', 'suv', 'pickup', 'mpv'];
                        foreach ($bodyOptions as $type) {
                            $isChecked = in_array($type, $currentBodies, true);
                            $checked   = $isChecked ? 'checked' : '';
                            $activeCls = $isChecked ? ' on' : '';
                            $label     = ucfirst($type);
                            echo "<label class=\"chip{$activeCls}\" for=\"body_{$type}\">";
                            echo "<input type=\"checkbox\" name=\"body[]\" value=\"{$type}\" id=\"body_{$type}\" {$checked}>";
                            echo "{$label}";
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>

                <div class="filter-group">
                    <span class="label"><i class="bi bi-fuel-pump"></i> Engine Type</span>
                    <div class="filter-options">
                        <?php
                        $engineTypeOptions = ['petrol' => 'Petrol', 'hybrid' => 'Hybrid', 'ev' => 'EV (Electric)'];
                        foreach ($engineTypeOptions as $val => $label) {
                            $isChecked = in_array($val, $currentEngines, true);
                            $activeCls = $isChecked ? ' on' : '';
                            $checked   = $isChecked ? 'checked' : '';
                            echo "<label class=\"chip{$activeCls}\" for=\"eng_{$val}\">";
                            echo "<input type=\"checkbox\" name=\"engine[]\" value=\"{$val}\" id=\"eng_{$val}\" {$checked}>";
                            echo "{$label}";
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>

                <div class="filter-group">
                    <span class="label"><i class="bi bi-tags"></i> Brand</span>
                    <div class="filter-options brand-scroll">
                        <?php foreach ($brandOptions as $brand): ?>
                            <?php
                            $brandId = preg_replace('/[^a-zA-Z0-9]/', '_', $brand);
                            $isChecked = in_array($brand, $currentBrands, true);
                            $activeCls = $isChecked ? ' on' : '';
                            ?>
                            <label class="chip<?= $activeCls ?>" for="brand_<?= htmlspecialchars($brandId, ENT_QUOTES) ?>">
                                <input type="checkbox" name="brand[]" value="<?= htmlspecialchars($brand, ENT_QUOTES) ?>" id="brand_<?= htmlspecialchars($brandId, ENT_QUOTES) ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
                                <?= htmlspecialchars($brand, ENT_QUOTES) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-group">
                    <span class="label"><i class="bi bi-cash-coin"></i> Downpayment</span>
                    <div class="input-wrap">
                        <span>RM</span>
                        <input type="number" id="downpaymentInput" name="deposit" min="0" step="1" placeholder="e.g. 5000" value="<?= htmlspecialchars($deposit, ENT_QUOTES); ?>">
                    </div>
                </div>

                <div class="filter-group">
                    <div class="field-label">
                        <span><i class="bi bi-percent"></i> Interest Rate</span>
                        <span id="interestLabel" class="value-pill"><?= htmlspecialchars(number_format($interest, 1, '.', ''), ENT_QUOTES); ?>%</span>
                    </div>
                    <input type="range" id="interestRate" name="interest" min="1.0" max="7.0" step="0.1" value="<?= htmlspecialchars(number_format($interest, 1, '.', ''), ENT_QUOTES); ?>">
                </div>

                <div class="filter-group">
                    <span class="label"><i class="bi bi-sort-down"></i> Sort Price</span>
                    <select name="sort" class="select-field">
                        <option value="asc" <?php echo $currentSort === 'asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="desc" <?php echo $currentSort === 'desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>

                <div class="filter-group d-none" id="evChargerSection">
                    <span class="label"><i class="bi bi-lightning-charge-fill"></i> EV Charger Search</span>
                    <div class="ev-box">
                        <p>Charging Stations</p>
                        <small>Find nearby charging stations instantly.</small>
                        <button type="button" id="evChargerBtn" class="btn good block">
                            <i class="bi bi-plug-fill"></i> Find Station
                        </button>
                    </div>
                </div>

                <div class="filter-group" style="border-bottom:none; padding-bottom:0;">
                    <div class="filter-actions">
                        <button type="submit" class="btn primary block"><i class="bi bi-search"></i> Apply Filters</button>
                    </div>
                </div>

                <input type="hidden" name="max_price" value="<?= htmlspecialchars($maxPrice, ENT_QUOTES); ?>">
                <input type="hidden" name="gaji" value="<?= htmlspecialchars($netSalary, ENT_QUOTES); ?>">
                <input type="hidden" name="loanYears" value="<?= htmlspecialchars($loanYears, ENT_QUOTES); ?>">
            </form>
        </aside>

        <div class="section-wrap">
            <div class="section-topbar">
                <div>
                    <h2 class="group-title">Available Matches</h2>
                    <span class="count-text">Showing <?= count($filteredCars) ?> vehicles within your range</span>
                </div>
                <span class="budget-badge" id="budgetBadge">
                    <i class="bi bi-wallet2 me-2"></i> Max Budget: RM <?= formatMoney($maxPrice) ?>
                </span>
            </div>

            <?php if (empty($filteredCars)): ?>
                <div class="no-results">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h4>No Match Found</h4>
                    <p>Try expanding your preferences, choosing alternative engine formats, or revising your target budget metrics.</p>
                </div>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($filteredCars as $index => $car): ?>
                        <?php
                        $sellingPrice = isset($car['price']) ? (float)$car['price'] : 0.0;

                        preg_match('/[0-9.]+(?=\s*L)/i', $car['engine'], $matches);
                        $engineSize = isset($matches[0]) ? (float)$matches[0] : 0.0;

                        $isElectric = strpos(strtolower($car['engine']), 'ev') !== false || strpos(strtolower($car['fuel']), 'electric') !== false;
                        $isHybrid = strpos(strtolower($car['fuel']), 'hybrid') !== false;

                        if ($isElectric) {
                            $mediaCls = 'media-electric';
                            $dotCls = 'electric';
                        } elseif ($engineSize >= 2.0 || strpos(strtolower($car['engine']), '2.0') !== false) {
                            $mediaCls = $isHybrid ? 'media-hybrid' : 'media-petrol';
                            $dotCls = $isHybrid ? 'hybrid' : 'petrol';
                        } elseif ($engineSize >= 1.6) {
                            $mediaCls = $isHybrid ? 'media-hybrid' : 'media-petrol';
                            $dotCls = $isHybrid ? 'hybrid' : 'petrol';
                        } else {
                            $mediaCls = $isHybrid ? 'media-hybrid' : 'media-petrol';
                            $dotCls = $isHybrid ? 'hybrid' : 'petrol';
                        }

                        $carIdKey = isset($car['car_id']) ? (int) $car['car_id'] : 0;
                        $roadTaxAnnual = isset($evRoadTaxByCarId[$carIdKey])
                            ? $evRoadTaxByCarId[$carIdKey]
                            : calculateMalaysianRoadTax($car['engine'], $car['fuel']);
                        $otrBasePrice = $sellingPrice + $roadTaxAnnual;
                        ?>
                        <div class="main-car-container">
                            <div class="car-card"
                                 data-otr-price="<?= $otrBasePrice ?>">

                                <div class="card-media <?= $mediaCls ?>">
                                    <div class="badges-row">
                                        <span class="tag">#<?= $index + 1 ?></span>
                                        <span></span>
                                    </div>
                                    <div class="sticker-recommend text-wrap">
                                        <span>Highly<br>Rec.</span>
                                    </div>

                                    <?php
                                    $imagePath = trim((string) ($car['image_path'] ?? ''));
                                    $imageFullPath = __DIR__ . '/' . ltrim($imagePath, '/');
                                    $hasImage = $imagePath !== '' && file_exists($imageFullPath);
                                    $imageSrc = $hasImage ? $imagePath . '?v=' . filemtime($imageFullPath) : '';
                                    ?>
                                    <?php if ($hasImage): ?>
                                        <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES) ?>"
                                             alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES) ?>">
                                    <?php else: ?>
                                        <i class="bi bi-car-front placeholder-icon"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body">
                                    <div class="kicker-row">
                                        <span class="kicker"><?= htmlspecialchars($car['body_type'], ENT_QUOTES) ?></span>
                                    </div>

                                    <h4 class="model-name"><?= htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES) ?></h4>

                                    <div class="drivetrain-row">
                                        <span class="dot <?= $dotCls ?>"></span>
                                        <span><?= htmlspecialchars(formatEngineDisplay($car['engine']) . ' (' . $car['fuel'] . ')', ENT_QUOTES) ?></span>
                                    </div>

                                    <?php if (!empty($car['recommended_use'])): ?>
                                        <div class="usage-row" title="Inferred by the fuel-efficiency rule engine from engine size and km/L">
                                            <i class="bi bi-signpost-2"></i>
                                            <span class="usage-tag"><?= htmlspecialchars($car['recommended_use'], ENT_QUOTES) ?></span>
                                            <?php if (!empty($car['fuel_economy'])): ?>
                                                <span>&middot; <?= htmlspecialchars($car['fuel_economy'], ENT_QUOTES) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="price-line price-toggle">
                                        <span>Retail Price
                                            <b>RM <?= formatMoney($otrBasePrice) ?></b>
                                        </span>
                                        <i class="bi bi-chevron-down toggle-icon"></i>
                                    </div>

                                    <div class="price-details">
                                        <div class="price-details-toolbar">
                                            <h5><?= htmlspecialchars($car['brand'] . ' ' . $car['model'], ENT_QUOTES) ?></h5>
                                            <button type="button" class="modal-close price-details-close" aria-label="Close">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

                                        <div class="price-box">
                                            <div class="row">
                                                <span class="muted-label">Selling Price:</span>
                                                <span class="val">RM <?= formatMoney($sellingPrice) ?></span>
                                            </div>
                                            <div class="row">
                                                <span class="muted-label">Annual Road Tax:</span>
                                                <span class="val">RM <?= number_format($roadTaxAnnual, 2) ?></span>
                                            </div>
                                            <div class="row total">
                                                <span class="muted-label" style="color:var(--good); font-weight:700;">Estimated OTR Price:</span>
                                                <span class="val total">RM <?= formatMoney($otrBasePrice) ?></span>
                                            </div>

                                            <div class="deposit-block car-downpayment-block" data-otr="<?= $otrBasePrice ?>">
                                                <div class="row" style="margin-bottom:0;">
                                                    <span class="muted-label" style="font-weight:700;">Deposit (10%):</span>
                                                    <span class="val standard-10-deposit">RM 0.00</span>
                                                </div>
                                                <div class="deposit-suggestion label-deposit-suggestion">
                                                    Evaluating financial allocation...
                                                </div>
                                            </div>

                                            <span class="fine-print">*Excludes Insurance, Registration, &amp; Ownership Endorsement fees.</span>
                                        </div>

                                        <div class="price-box installment-box car-installment-block">
                                            <div class="installment-title">Estimated Monthly Installment</div>
                                            <div class="row">
                                                <span class="muted-label">7 Years:</span>
                                                <span class="val estimate-7y">RM 0.00 / mo</span>
                                            </div>
                                            <div class="row">
                                                <span class="muted-label">8 Years:</span>
                                                <span class="val estimate-8y">RM 0.00 / mo</span>
                                            </div>
                                            <div class="row">
                                                <span class="muted-label">9 Years:</span>
                                                <span class="val estimate-9y">RM 0.00 / mo</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-actions">
                                        <a href="<?php $link = htmlspecialchars($car['link'], ENT_QUOTES);
                                                    echo strpos($link, 'http') === 0 ? $link : 'https://' . $link; ?>" target="_blank" class="btn primary">
                                            Go <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="priceDetailsBackdrop" class="price-details-backdrop"></div>

    <footer>
        <div class="foot-inner">
            <p>Car Finder — recommendations and pricing shown are estimates only, based on the filters and financing details you provide.</p>
        </div>
    </footer>

    <!-- EV Charger Station Finder Modal -->
    <div id="evChargerModal" class="modal-overlay">
        <div class="modal-box ev">
            <div class="modal-toolbar">
                <h5><i class="bi bi-plug-fill me-2" style="color:var(--good);"></i> Find EV Charger Station</h5>
                <button type="button" class="modal-close" id="closeEvChargerBtn" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <span class="label" style="display:block;">Your Location</span>
                <div class="ev-input-row">
                    <input type="text" id="evChargerLocationInput" placeholder="e.g. Serendah, Selangor or Seksyen 18, Shah Alam">
                    <button type="button" id="evChargerSearchBtn" class="btn good">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
                <p id="evChargerFormError" class="text-error" style="display:none;"></p>

                <div id="evChargerLoading" class="ev-loading d-none">
                    <span class="ev-spinner"></span> Looking up nearby charging stations…
                </div>

                <p id="evChargerApiError" class="box-error" style="display:none;"></p>
                <p id="evChargerApiMessage" class="box-warning" style="display:none;"></p>
                <p id="evChargerEmpty" class="label" style="display:none;">No EV charging stations were found near that location.</p>

                <div id="evChargerResultsList"></div>
            </div>
        </div>
    </div>

    <script>
        const interestRate = document.getElementById('interestRate');
        const interestLabel = document.getElementById('interestLabel');
        const downpaymentInput = document.getElementById('downpaymentInput');
        const budgetBadge = document.getElementById('budgetBadge');
        const hiddenSalary = document.querySelector('input[name="gaji"]');
        const hiddenYears = document.querySelector('input[name="loanYears"]');

        function formatMoney(value) {
            return Number(value).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function calculateMonthlyInstallment(principal, years, annualInterestRate) {
            if (principal <= 0) return 0;
            const totalInterest = principal * (annualInterestRate / 100) * years;
            const totalPayable = principal + totalInterest;
            const totalMonths = years * 12;
            return totalPayable / totalMonths;
        }

        function calculateBudget(salary, deposit, years, interest) {
            const netSalary = parseFloat(salary) || 0;
            const depositAmt = parseFloat(deposit) || 0;
            const loanYears = parseInt(years, 10) || 0;
            const annualRate = parseFloat(interest) || 2.5;
            if (netSalary <= 0 || loanYears <= 0) {
                return 0;
            }
            const maxMonthlyPayment = netSalary * 0.18;
            const monthlyRate = (annualRate / 100) / 12;
            const totalPayments = loanYears * 12;
            let maxLoanAmount;
            if (monthlyRate === 0) {
                maxLoanAmount = maxMonthlyPayment * totalPayments;
            } else {
                maxLoanAmount = maxMonthlyPayment * ((1 - Math.pow(1 + monthlyRate, -totalPayments)) / monthlyRate);
            }
            return maxLoanAmount + depositAmt;
        }

        function updateBudgetAndInstallments() {
            const dynamicInterest = parseFloat(interestRate.value) || 2.5;
            const userDeposit = parseFloat(downpaymentInput.value) || 0;
            const userSalary = parseFloat(hiddenSalary.value) || 0;

            // Highlight recommendation threshold rule: 16% of Net Salary
            const threshold16Percent = userSalary * 0.16;

            interestLabel.textContent = `${dynamicInterest.toFixed(1)}%`;

            // 1. Update primary configuration balance meter
            const newBudget = calculateBudget(userSalary, userDeposit, hiddenYears.value, interestRate.value);
            if (newBudget > 0) {
                budgetBadge.innerHTML = `<i class="bi bi-wallet2 me-2"></i> Max Budget: RM ${formatMoney(newBudget)}`;
            }

            // 2. Loop through deposit blocks
            document.querySelectorAll('.car-downpayment-block').forEach(block => {
                const otrPrice = parseFloat(block.dataset.otr) || 0;
                const standardTenPercent = otrPrice * 0.10;

                block.querySelector('.standard-10-deposit').textContent = `RM ${formatMoney(standardTenPercent)}`;

                const suggestionEl = block.querySelector('.label-deposit-suggestion');
                if (userDeposit < standardTenPercent) {
                    const topupNeeded = standardTenPercent - userDeposit;
                    suggestionEl.className = "deposit-suggestion label-deposit-suggestion";
                    suggestionEl.style.background = "#fdeaea";
                    suggestionEl.style.color = "var(--danger)";
                    suggestionEl.innerHTML = `<i class="bi bi-info-circle"></i> Add RM ${formatMoney(topupNeeded)} more to satisfy standard 10% downpayment.`;
                } else if (userDeposit > standardTenPercent) {
                    suggestionEl.className = "deposit-suggestion label-deposit-suggestion";
                    suggestionEl.style.background = "#e6f7f0";
                    suggestionEl.style.color = "var(--good)";
                    suggestionEl.innerHTML = `<i class="bi bi-check-circle"></i> Great! Safe choice. Your higher downpayment reduces loan interest.`;
                } else {
                    suggestionEl.className = "deposit-suggestion label-deposit-suggestion";
                    suggestionEl.style.background = "#fff";
                    suggestionEl.style.color = "var(--muted)";
                    suggestionEl.innerHTML = `<i class="bi bi-dash-circle"></i> Exactly matching minimum 10% downpayment requirements.`;
                }
            });

            // 3. Loop through main car containers to set installments and evaluate the recommendation status
            document.querySelectorAll('.main-car-container').forEach(container => {
                const installmentBlock = container.querySelector('.car-installment-block');
                const cardElement = container.querySelector('.car-card');
                const stickerElement = container.querySelector('.sticker-recommend');

                const otrPrice = parseFloat(cardElement.dataset.otrPrice) || 0;
                const dynamicPrincipal = Math.max(0, otrPrice - userDeposit);
                const pay9y = calculateMonthlyInstallment(dynamicPrincipal, 9, dynamicInterest);

                // Recommendation Verification logic step (9 years installment <= 16% of salary)
                if (userSalary > 0 && pay9y > 0 && pay9y <= threshold16Percent) {
                    stickerElement.style.display = 'flex';
                    cardElement.classList.add('border-highly-recommend');
                } else {
                    stickerElement.style.display = 'none';
                    cardElement.classList.remove('border-highly-recommend');
                }

                if (!installmentBlock) return;

                const pay7y = calculateMonthlyInstallment(dynamicPrincipal, 7, dynamicInterest);
                const pay8y = calculateMonthlyInstallment(dynamicPrincipal, 8, dynamicInterest);

                installmentBlock.querySelector('.estimate-7y').textContent = `RM ${formatMoney(pay7y)} / mo`;
                installmentBlock.querySelector('.estimate-8y').textContent = `RM ${formatMoney(pay8y)} / mo`;
                installmentBlock.querySelector('.estimate-9y').textContent = `RM ${formatMoney(pay9y)} / mo`;
            });
        }

        interestRate.addEventListener('input', updateBudgetAndInstallments);
        downpaymentInput.addEventListener('input', updateBudgetAndInstallments);

        updateBudgetAndInstallments();

        // ── Chip Toggle Visual (Body / Engine / Brand) ───────────────────
        document.querySelectorAll('.chip input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const label = this.closest('.chip');
                if (label) {
                    label.classList.toggle('on', this.checked);
                }
                if (this.name === 'engine[]' && this.value === 'ev') {
                    refreshEvChargerVisibility();
                }
            });
        });

        // ── Clear Filters Button ─────────────────────────────────────────
        document.getElementById('clearFiltersBtn').addEventListener('click', function() {
            // Re-check ALL body type chips and restore their active style
            document.querySelectorAll('input[name="body[]"]').forEach(function(cb) {
                cb.checked = true;
                const label = cb.closest('.chip');
                if (label) label.classList.add('on');
            });

            // Uncheck all engine filter chips
            document.querySelectorAll('input[name="engine[]"]').forEach(function(cb) {
                cb.checked = false;
                const label = cb.closest('.chip');
                if (label) label.classList.remove('on');
            });

            // Uncheck all brand filter chips
            document.querySelectorAll('input[name="brand[]"]').forEach(function(cb) {
                cb.checked = false;
                const label = cb.closest('.chip');
                if (label) label.classList.remove('on');
            });

            // Reset sort, deposit, interest
            document.querySelector('select[name="sort"]').value = 'asc';
            downpaymentInput.value = '';
            interestRate.value = '2.5';
            interestLabel.textContent = '2.5%';

            // Hide EV charger section if visible
            refreshEvChargerVisibility();

            updateBudgetAndInstallments();
        });

        // ── EV Charger Station Finder ────────────────────────────────────
        const evChargerSection = document.getElementById('evChargerSection');
        const evChargerBtn = document.getElementById('evChargerBtn');
        const evChargerModal = document.getElementById('evChargerModal');
        const closeEvChargerBtn = document.getElementById('closeEvChargerBtn');
        const evChargerLocationInput = document.getElementById('evChargerLocationInput');
        const evChargerSearchBtn = document.getElementById('evChargerSearchBtn');
        const evChargerFormError = document.getElementById('evChargerFormError');
        const evChargerLoading = document.getElementById('evChargerLoading');
        const evChargerApiError = document.getElementById('evChargerApiError');
        const evChargerApiMessage = document.getElementById('evChargerApiMessage');
        const evChargerEmpty = document.getElementById('evChargerEmpty');
        const evChargerResultsList = document.getElementById('evChargerResultsList');

        // Show/hide the "Find EV Charger Station" box based on the EV engine-type filter.
        function refreshEvChargerVisibility() {
            const evCheckbox = document.getElementById('eng_ev');
            if (!evCheckbox || !evChargerSection) return;
            evChargerSection.classList.toggle('d-none', !evCheckbox.checked);
        }
        refreshEvChargerVisibility();

        function openEvChargerModal() {
            evChargerModal.classList.add('show');
            evChargerLocationInput.focus();
        }

        function closeEvChargerModal() {
            evChargerModal.classList.remove('show');
        }

        if (evChargerBtn) {
            evChargerBtn.addEventListener('click', openEvChargerModal);
        }
        closeEvChargerBtn.addEventListener('click', closeEvChargerModal);
        evChargerModal.addEventListener('click', (e) => {
            if (e.target === evChargerModal) closeEvChargerModal();
        });

        function resetEvChargerResultsUI() {
            evChargerFormError.style.display = 'none';
            evChargerApiError.style.display = 'none';
            evChargerApiMessage.style.display = 'none';
            evChargerEmpty.style.display = 'none';
            evChargerResultsList.innerHTML = '';
        }

        function renderEvChargerStation(station, index) {
            const card = document.createElement('div');
            card.className = 'ev-station-card';

            const details = [];
            if (station.distance_km !== null && station.distance_km !== undefined) {
                details.push(`Distance: ${station.distance_km} km`);
            }
            details.push(`Address: ${station.address || 'Not available'}`);
            if (station.operator) details.push(`Operator: ${station.operator}`);
            if (station.charger_type) details.push(`Charger Type: ${station.charger_type}`);
            if (station.num_points) details.push(`Charging Points: ${station.num_points}`);

            card.innerHTML = `
                <p>${index + 1}. ${station.name}</p>
                <ul>
                    ${details.map(d => `<li>${d}</li>`).join('')}
                </ul>
            `;
            return card;
        }

        async function searchEvChargers() {
            const location = evChargerLocationInput.value.trim();
            resetEvChargerResultsUI();

            if (!location) {
                evChargerFormError.textContent = 'Please enter a location.';
                evChargerFormError.style.display = 'block';
                return;
            }

            evChargerSearchBtn.disabled = true;
            evChargerLoading.classList.remove('d-none');
            evChargerLoading.style.display = 'flex';

            try {
                // Step 1: geocode the typed location to lat/lng
                const geoResponse = await fetch('geocode.php?address=' + encodeURIComponent(location));
                if (!geoResponse.ok) {
                    throw new Error(`Server returned HTTP ${geoResponse.status}.`);
                }
                const geoData = await geoResponse.json();
                if (!geoData.success) {
                    evChargerApiError.textContent = geoData.error || 'Could not find that location.';
                    evChargerApiError.style.display = 'block';
                    return;
                }

                // Step 2: look up nearby EV charging stations for those coordinates
                const stationResponse = await fetch(`get_ev_chargers.php?lat=${encodeURIComponent(geoData.lat)}&lon=${encodeURIComponent(geoData.lng)}`);
                if (!stationResponse.ok) {
                    throw new Error(`Server returned HTTP ${stationResponse.status}.`);
                }
                const stationData = await stationResponse.json();

                if (!stationData.success) {
                    evChargerApiError.textContent = stationData.error || 'Could not load nearby EV charging stations right now.';
                    evChargerApiError.style.display = 'block';
                    return;
                }

                if (stationData.message) {
                    evChargerApiMessage.textContent = stationData.message;
                    evChargerApiMessage.style.display = 'block';
                }

                if (!stationData.stations || stationData.stations.length === 0) {
                    if (!stationData.message) {
                        evChargerEmpty.style.display = 'block';
                    }
                    return;
                }

                stationData.stations.forEach((station, index) => {
                    evChargerResultsList.appendChild(renderEvChargerStation(station, index));
                });

            } catch (err) {
                evChargerApiError.textContent = err.message || 'Network error while searching for EV charging stations.';
                evChargerApiError.style.display = 'block';
            } finally {
                evChargerSearchBtn.disabled = false;
                evChargerLoading.classList.add('d-none');
                evChargerLoading.style.display = 'none';
            }
        }

        evChargerSearchBtn.addEventListener('click', searchEvChargers);
        evChargerLocationInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchEvChargers();
            }
        });

        // ── Card Pricing/Installment Popup ─────────────────────────────────────
        // The price breakdown + monthly installment panel is hidden by default
        // (see .price-details CSS) and pops up centered over the page, with the
        // rest of the page dimmed behind a shared backdrop, when a card is clicked.
        // Values inside are kept current by updateBudgetAndInstallments(), which
        // already runs on load and on every deposit/interest change.
        const priceDetailsBackdrop = document.getElementById('priceDetailsBackdrop');

        function closeExpandedCard() {
            document.querySelectorAll('.car-card.expanded').forEach(c => c.classList.remove('expanded'));
            priceDetailsBackdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.car-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't toggle when clicking a real link/button, or inside the popped-up panel itself
                if (e.target.closest('a, .btn') || e.target.closest('.price-details')) {
                    return;
                }
                const alreadyExpanded = this.classList.contains('expanded');
                closeExpandedCard();
                if (!alreadyExpanded) {
                    this.classList.add('expanded');
                    priceDetailsBackdrop.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            });

            card.querySelector('.price-details-close')?.addEventListener('click', function(e) {
                e.stopPropagation();
                closeExpandedCard();
            });
        });

        priceDetailsBackdrop.addEventListener('click', closeExpandedCard);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeExpandedCard();
        });

    </script>
    <script src="timezone-widget.js"></script>
</body>

</html>
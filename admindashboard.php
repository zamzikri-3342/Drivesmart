<?php
require 'db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$update_mode = false;
$id = $brand = $model = $body_type = $engine = $fuel = $price = $link = "";
$power_output_kw = "";
$km_per_liter = "";
$full_tank_liter = "";
$image_path = "";
$form_errors = [];

/** Null-safe htmlspecialchars shorthand, used everywhere we echo a value. */
function h($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES);
}

// Flash message support (survives the redirect after add/update/delete)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Parse a free-text engine value ("1.5L", "1498", "1498cc") into a plain CC integer.
 * Returns null when the input is blank or unrecognisable (e.g. EV cars with no engine).
 */
function parseEngineToCc(string $input): ?int
{
    $input = trim($input);
    if ($input === '') {
        return null;
    }
    if (!preg_match('/^([\d.]+)\s*(cc|l)?$/i', $input, $m)) {
        return null;
    }
    $value = (float) $m[1];
    $unit = strtolower($m[2] ?? '');
    if ($unit === 'l' || ($unit === '' && $value < 10)) {
        return (int) round($value * 1000);
    }
    return (int) round($value);
}

/** Strip characters that aren't safe in a Windows/Unix filename or folder name. */
function sanitizeForFilename(string $value): string
{
    $value = trim($value);
    $value = preg_replace('/[\\\\\/:*?"<>|]/', '', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return $value !== '' ? $value : 'car';
}

/**
 * Save the admin's originally-picked file (untouched — no re-encoding/cropping)
 * to Images/{Brand}/{Model}.{ext}, matching the existing image folder convention.
 * Returns the relative image path, or null if the upload wasn't a real image.
 */
function saveUploadedCarImage(array $file, string $brand, string $model, int $carId, PDO $pdo): ?string
{
    if (!is_uploaded_file($file['tmp_name']) || @getimagesize($file['tmp_name']) === false) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
        $ext = 'jpg';
    }

    $safeBrand = sanitizeForFilename($brand);
    $safeModel = sanitizeForFilename($model);
    $relDir = 'Images/' . $safeBrand;
    $dir = __DIR__ . '/' . $relDir;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $relPath = $relDir . '/' . $safeModel . '.' . $ext;

    // Don't clobber a different car's photo that happens to share this brand+model filename.
    $existingStmt = $pdo->prepare('SELECT image_path FROM cars WHERE id = ?');
    $existingStmt->execute([$carId]);
    $currentPath = $existingStmt->fetchColumn();
    if (file_exists(__DIR__ . '/' . $relPath) && $currentPath !== $relPath) {
        $relPath = $relDir . '/' . $safeModel . '-' . $carId . '.' . $ext;
    }

    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $relPath)) {
        return null;
    }

    return $relPath;
}

// 1. DELETE ACTION
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->execute([$delete_id]);
    if ($stmt->rowCount() > 0) {
        setFlash('success', "Car #{$delete_id} was deleted.");
    } else {
        setFlash('error', "Couldn't find that car to delete.");
    }
    header('Location: admindashboard.php');
    exit;
}

// 2. FETCH CAR DATA FOR UPDATE MODE
if (isset($_GET['edit'])) {
    $update_mode = true;
    $edit_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$edit_id]);
    $car = $stmt->fetch();

    if ($car) {
        $id = $car['id'];
        $brand = $car['brand'];
        $model = $car['model'];
        $body_type = $car['body_type'];
        $fuel = $car['fuel'];
        $price = $car['price'];
        $link = $car['link'];
        $image_path = $car['image_path'] ?? '';

        if (strcasecmp($fuel, 'EV') === 0) {
            $evStmt = $pdo->prepare("SELECT power_output_kw FROM ev_cars WHERE id = ?");
            $evStmt->execute([$id]);
            $evRow = $evStmt->fetch();
            if ($evRow) {
                $power_output_kw = $evRow['power_output_kw'];
            }
        }

        // Fetch fuel efficiency data (engine size lives here as engine_cc, not on the cars table)
        $fuelEffStmt = $pdo->prepare("SELECT km_per_liter, full_tank_liters, engine_cc FROM car_fuel_efficiency WHERE car_id = ?");
        $fuelEffStmt->execute([$id]);
        $fuelEffRow = $fuelEffStmt->fetch();
        if ($fuelEffRow) {
            $km_per_liter = $fuelEffRow['km_per_liter'];
            $full_tank_liter = $fuelEffRow['full_tank_liters'];
            $engine = $fuelEffRow['engine_cc'] !== null ? $fuelEffRow['engine_cc'] . ' CC' : '';
        }
    }
}

// 3. ADD OR UPDATE POST ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $body_type = $_POST['body_type'] ?? '';
    $engine = trim($_POST['engine'] ?? '');
    $fuel = $_POST['fuel'] ?? '';
    $price = $_POST['price'] ?? '';
    $link = trim($_POST['link'] ?? '');
    $isEv = strcasecmp($fuel, 'EV') === 0;
    $power_output_kw = $isEv ? (($_POST['power_output_kw'] ?? '') !== '' ? (float) $_POST['power_output_kw'] : null) : null;
    $engineCc = $isEv ? null : parseEngineToCc($engine);

    // Fuel efficiency data
    $km_per_liter = ($_POST['km_per_liter'] ?? '') !== '' ? (float) $_POST['km_per_liter'] : null;
    $full_tank_liter = ($_POST['full_tank_liter'] ?? '') !== '' ? (float) $_POST['full_tank_liter'] : null;

    // ---- Server-side validation ----
    if ($brand === '') $form_errors[] = 'Brand is required.';
    if ($model === '') $form_errors[] = 'Model is required.';
    if ($body_type === '') $form_errors[] = 'Body type is required.';
    if ($fuel === '') $form_errors[] = 'Fuel type is required.';
    if ($price === '' || !is_numeric($price) || (float) $price < 0) $form_errors[] = 'Enter a valid, non-negative price.';
    if ($link === '') $form_errors[] = 'Car link is required.';
    if ($isEv && $power_output_kw !== null && $power_output_kw < 0) $form_errors[] = 'Power output (kW) cannot be negative.';
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK && $_FILES['car_image']['size'] > 5 * 1024 * 1024) {
        $form_errors[] = 'Car image must be smaller than 5MB.';
    }
    // Fuel efficiency (km/L, full tank) only applies to non-EV cars — EVs don't have a
    // row in car_fuel_efficiency, so require it for Petrol/Hybrid/Diesel and ignore it for EV.
    if (!$isEv) {
        if ($km_per_liter === null || $km_per_liter <= 0) $form_errors[] = 'km per Liter is required for non-EV cars.';
        if ($full_tank_liter === null || $full_tank_liter <= 0) $form_errors[] = 'Full tank (L) is required for non-EV cars.';
    } else {
        // EVs never store fuel-efficiency data.
        $km_per_liter = null;
        $full_tank_liter = null;
    }

    if (!empty($form_errors)) {
        $update_mode = isset($_POST['update']);
        $id = $_POST['id'] ?? '';
        setFlash('error', implode(' ', $form_errors));
        // Fall through to render the page below with the submitted values pre-filled.
    } else {
        if (isset($_POST['update'])) {
            $car_id = $_POST['id'];
            $sql = "UPDATE cars SET brand=?, model=?, body_type=?, fuel=?, price=?, link=?, last_modified_by=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$brand, $model, $body_type, $fuel, $price, $link, $admin_id, $car_id]);
        } else {
            $sql = "INSERT INTO cars (brand, model, body_type, fuel, price, link, last_modified_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$brand, $model, $body_type, $fuel, $price, $link, $admin_id]);
            $car_id = $pdo->lastInsertId();
        }

        // Save the uploaded car photo, if one was picked this submission (original file, untouched)
        $imageError = null;
        if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK) {
            $newImagePath = saveUploadedCarImage($_FILES['car_image'], $brand, $model, (int) $car_id, $pdo);
            if ($newImagePath !== null) {
                $imgStmt = $pdo->prepare("UPDATE cars SET image_path = ? WHERE id = ?");
                $imgStmt->execute([$newImagePath, $car_id]);
            } else {
                $imageError = 'the photo could not be saved (unsupported file), but the car details were saved';
            }
        }

        if ($isEv && $power_output_kw !== null) {
            // Save (or update) the power output for this EV in the ev_cars table.
            // Uses ON DUPLICATE KEY UPDATE so existing rows (and their roadtax value) aren't wiped out.
            $evSql = "INSERT INTO ev_cars (id, power_output_kw) VALUES (?, ?)
                      ON DUPLICATE KEY UPDATE power_output_kw = VALUES(power_output_kw)";
            $evStmt = $pdo->prepare($evSql);
            $evStmt->execute([$car_id, $power_output_kw]);
        } elseif (!$isEv) {
            // If a car was previously EV and got switched to another fuel type, drop its ev_cars row.
            $evDeleteStmt = $pdo->prepare("DELETE FROM ev_cars WHERE id = ?");
            $evDeleteStmt->execute([$car_id]);
        }

        // Save fuel efficiency data (and engine size) if provided; EVs don't have a row here.
        if ($km_per_liter !== null || $full_tank_liter !== null || $engineCc !== null) {
            $fuelEffSql = "INSERT INTO car_fuel_efficiency (car_id, km_per_liter, full_tank_liters, engine_cc) VALUES (?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE km_per_liter = VALUES(km_per_liter), full_tank_liters = VALUES(full_tank_liters), engine_cc = VALUES(engine_cc)";
            $fuelEffStmt = $pdo->prepare($fuelEffSql);
            $fuelEffStmt->execute([$car_id, $km_per_liter, $full_tank_liter, $engineCc]);
        } elseif ($isEv) {
            // If a car was previously non-EV and got switched to EV, drop its old fuel-efficiency row.
            $fuelEffDeleteStmt = $pdo->prepare("DELETE FROM car_fuel_efficiency WHERE car_id = ?");
            $fuelEffDeleteStmt->execute([$car_id]);
        }

        $actionWord = isset($_POST['update']) ? 'updated' : 'added';
        $carLabel = trim("{$brand} {$model}");
        if ($imageError !== null) {
            setFlash('error', ucfirst($actionWord) . " \"{$carLabel}\", but {$imageError}.");
        } else {
            setFlash('success', "\"{$carLabel}\" was successfully {$actionWord}.");
        }

        header('Location: admindashboard.php');
        exit;
    }
}

// 4. FETCH ALL CARS TO DISPLAY
$query = "SELECT cars.*, admins.username AS modified_by, ev_cars.power_output_kw, car_fuel_efficiency.engine_cc
          FROM cars
          LEFT JOIN admins ON cars.last_modified_by = admins.admin_id
          LEFT JOIN ev_cars ON cars.id = ev_cars.id
          LEFT JOIN car_fuel_efficiency ON cars.id = car_fuel_efficiency.car_id";
$cars = $pdo->query($query)->fetchAll();

// Quick stats for the toolbar
$totalCars = count($cars);
$evCount = 0;
$brandSet = [];
$priceSum = 0.0;
foreach ($cars as $c) {
    if (strcasecmp($c['fuel'], 'EV') === 0) $evCount++;
    $brandSet[$c['brand']] = true;
    $priceSum += (float) $c['price'];
}
$avgPrice = $totalCars > 0 ? $priceSum / $totalCars : 0;
$brandCount = count($brandSet);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <style>
        /* Matches the aspect ratio of .car-img-placeholder in cars.php (180px tall card image) */
        .car-image-preview-box {
            width: 220px;
            height: 99px;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            color: #9ca3af;
            font-size: 1.75rem;
        }

        .car-image-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #cropperTarget {
            display: block;
            max-width: 100%;
        }

        .cropper-modal-wrapper {
            max-height: 55vh;
            overflow: hidden;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #f3f4f6;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
        }

        .field-error {
            border-color: #f87171 !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-6">

<div class="max-w-7xl mx-auto">

    <div class="bg-gray-800 text-white px-6 py-4 rounded-xl flex justify-between items-center shadow-md mb-6">
        <h2 class="text-xl font-semibold">Welcome, <span class="text-blue-400"><?= h($_SESSION['username']) ?></span>!</h2>
        <div class="flex items-center gap-3">
            <div data-timezone-widget></div>
            <a href="view_cars.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg font-medium transition duration-200">View Cars</a>
            <a href="login.php?logout=1" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg font-medium transition duration-200">Logout</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="mb-6 px-4 py-3 rounded-lg border flex items-start gap-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-700' ?>">
            <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> mt-0.5"></i>
            <span class="text-sm font-medium"><?= h($flash['message']) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Cars</p>
            <p class="text-2xl font-bold text-gray-800"><?= $totalCars ?></p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">EV Models</p>
            <p class="text-2xl font-bold text-gray-800"><?= $evCount ?></p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Brands</p>
            <p class="text-2xl font-bold text-gray-800"><?= $brandCount ?></p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Avg. Price</p>
            <p class="text-2xl font-bold text-gray-800">RM <?= number_format($avgPrice, 0) ?></p>
        </div>
    </div>

    <div id="carFormSection" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="bi <?= $update_mode ? 'bi-pencil-square text-emerald-600' : 'bi-plus-circle text-blue-600' ?>"></i>
                <?= $update_mode ? 'Update Car Details' : 'Add New Car' ?>
            </h3>
            <?php if ($update_mode): ?>
                <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">Editing car #<?= h($id) ?></span>
            <?php endif; ?>
        </div>
        <form method="POST" action="admindashboard.php" enctype="multipart/form-data" id="carForm">
            <input type="hidden" name="id" value="<?= h($id) ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Brand *</label>
                    <input type="text" name="brand" placeholder="e.g. Perodua" value="<?= h($brand) ?>" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Model *</label>
                    <input type="text" name="model" placeholder="e.g. Bezza" value="<?= h($model) ?>" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Body Type *</label>
                    <select name="body_type" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full" required>
                        <option value="">Select Body Type</option>
                        <?php foreach (['Sedan', 'Hatchback', 'SUV', 'MPV', 'Pickup'] as $bodyOption): ?>
                            <option value="<?= h($bodyOption) ?>" <?= $body_type === $bodyOption ? 'selected' : '' ?>><?= $bodyOption ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Fuel Type *</label>
                    <select name="fuel" id="fuelSelect" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full" required>
                        <option value="">Select Fuel Type</option>
                        <?php foreach (['Petrol', 'Hybrid', 'EV', 'Diesel'] as $fuelOption): ?>
                            <option value="<?= h($fuelOption) ?>" <?= $fuel === $fuelOption ? 'selected' : '' ?>><?= $fuelOption ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="engineFieldWrapper" class="w-full">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Engine</label>
                    <input type="text" name="engine" id="engineInput" placeholder="e.g. 1.5L" value="<?= h($engine ?? '') ?>" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full">
                </div>
                <div id="kwFieldWrapper" class="w-full hidden">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Power Output</label>
                    <div class="flex gap-2">
                        <input type="number" step="0.1" min="0" name="power_output_kw" id="kwInput" placeholder="e.g. 120" value="<?= h($power_output_kw ?? '') ?>" class="p-2 border rounded-lg flex-1 outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" id="addKwBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-lg font-medium whitespace-nowrap">+ kW</button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Power output in kW (saved to EV specs)</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Price (RM) *</label>
                    <input type="number" step="0.01" min="0" name="price" placeholder="e.g. 46500" value="<?= h($price) ?>" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full" required>
                </div>
                <div class="sm:col-span-2 md:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Car Link (URL) *</label>
                    <input type="text" name="link" placeholder="e.g. perodua.com.my/our-models/sedan/bezza" value="<?= h($link) ?>" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full">
                </div>
            </div>

            <div id="fuelEfficiencySection" class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="sm:col-span-2 md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fuel Efficiency Info <span class="text-xs text-gray-400 font-normal">(required for Petrol / Hybrid / Diesel — not applicable to EV)</span></label>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">km per Liter <span id="kmPerLiterRequiredMark">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="km_per_liter" id="kmPerLiterInput" placeholder="e.g. 18.5" value="<?= h($km_per_liter) ?>" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full" title="Average fuel consumption (kilometers per liter)">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Full Tank (L) <span id="fullTankRequiredMark">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="full_tank_liter" id="fullTankInput" placeholder="e.g. 40" value="<?= h($full_tank_liter) ?>" class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-full" title="Total fuel tank capacity in liters">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Car Image</label>
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div id="imagePreviewBox" class="car-image-preview-box">
                        <?php $imageFullPath = __DIR__ . '/' . ltrim($image_path, '/'); ?>
                        <?php if ($image_path !== '' && file_exists($imageFullPath)): ?>
                            <img id="imagePreviewImg" src="<?= h($image_path . '?v=' . filemtime($imageFullPath)) ?>" alt="">
                        <?php else: ?>
                            <i id="imagePreviewIcon" class="bi bi-image"></i>
                            <img id="imagePreviewImg" src="" alt="" class="hidden">
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="car_image" id="carImageFileInput" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-medium hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-2">Pick a photo, then preview how it'll be cropped/zoomed on the Recommended Cars page. The original file is uploaded as-is — the preview doesn't alter it. Leave blank to keep the current image.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <?php if ($update_mode): ?>
                    <button type="submit" name="update" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">Update Car</button>
                    <a href="admindashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">Add Car</button>
                <?php endif; ?>
            </div>
        </form>

        <!-- Image crop/zoom modal -->
        <div id="cropperBackdrop" class="fixed inset-0 z-40 hidden bg-black/50"></div>
        <div id="cropperModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl p-4">
                    <h4 class="text-base font-bold text-gray-800 mb-3">Crop & Zoom Car Image</h4>
                    <div class="cropper-modal-wrapper bg-gray-100 rounded-lg">
                        <img id="cropperTarget" src="" alt="">
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Zoom</label>
                        <input type="range" id="zoomRange" min="0" max="1" step="0.01" value="0" class="w-full accent-blue-600">
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Drag to reposition, use the slider (or mouse wheel / pinch) to zoom. The frame matches the image box shown on the Recommended Cars page.</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" id="cropperCancelBtn" class="px-4 py-2 rounded-lg font-medium text-gray-600 hover:bg-gray-100">Cancel</button>
                        <button type="button" id="cropperConfirmBtn" class="px-4 py-2 rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700">Use This Image</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const fuelSelect = document.getElementById('fuelSelect');
            const engineFieldWrapper = document.getElementById('engineFieldWrapper');
            const kwFieldWrapper = document.getElementById('kwFieldWrapper');
            const engineInput = document.getElementById('engineInput');
            const kwInput = document.getElementById('kwInput');
            const addKwBtn = document.getElementById('addKwBtn');
            const fuelEfficiencySection = document.getElementById('fuelEfficiencySection');
            const kmPerLiterInput = document.getElementById('kmPerLiterInput');
            const fullTankInput = document.getElementById('fullTankInput');
            const kmPerLiterRequiredMark = document.getElementById('kmPerLiterRequiredMark');
            const fullTankRequiredMark = document.getElementById('fullTankRequiredMark');

            function toggleEngineOrKw() {
                const isEv = fuelSelect.value === 'EV';

                engineFieldWrapper.classList.toggle('hidden', isEv);
                kwFieldWrapper.classList.toggle('hidden', !isEv);
                if (isEv) {
                    engineInput.value = ''; // EVs don't use an engine CC/L value
                } else {
                    kwInput.value = '';
                }

                // Fuel efficiency (km/L, full tank) doesn't apply to EVs — hide it and
                // drop the "required" constraint so the form can still be submitted.
                fuelEfficiencySection.classList.toggle('hidden', isEv);
                kmPerLiterInput.required = !isEv;
                fullTankInput.required = !isEv;
                kmPerLiterRequiredMark.classList.toggle('hidden', isEv);
                fullTankRequiredMark.classList.toggle('hidden', isEv);
                if (isEv) {
                    kmPerLiterInput.value = '';
                    fullTankInput.value = '';
                }
            }

            addKwBtn.addEventListener('click', function() {
                kwInput.focus();
            });

            fuelSelect.addEventListener('change', toggleEngineOrKw);
            toggleEngineOrKw(); // run on page load too, so Edit mode shows the right field

            // ---- Image crop/zoom (matches the .car-img-placeholder box on cars.php) ----
            const CROP_ASPECT_RATIO = 20 / 9; // 220x99 preview box above
            const CROP_OUTPUT_WIDTH = 800;
            const CROP_OUTPUT_HEIGHT = Math.round(CROP_OUTPUT_WIDTH / CROP_ASPECT_RATIO);

            const carImageFileInput = document.getElementById('carImageFileInput');
            const imagePreviewBox = document.getElementById('imagePreviewBox');
            const imagePreviewImg = document.getElementById('imagePreviewImg');
            const imagePreviewIcon = document.getElementById('imagePreviewIcon');
            const cropperBackdrop = document.getElementById('cropperBackdrop');
            const cropperModal = document.getElementById('cropperModal');
            const cropperTarget = document.getElementById('cropperTarget');
            const zoomRange = document.getElementById('zoomRange');
            const cropperCancelBtn = document.getElementById('cropperCancelBtn');
            const cropperConfirmBtn = document.getElementById('cropperConfirmBtn');

            let cropper = null;

            function openCropper(imageSrc) {
                cropperTarget.src = imageSrc;
                cropperBackdrop.classList.remove('hidden');
                cropperModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(cropperTarget, {
                    aspectRatio: CROP_ASPECT_RATIO,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    background: false,
                    zoomOnWheel: true,
                    ready() {
                        zoomRange.value = 0;
                    }
                });
            }

            function closeCropper() {
                cropperBackdrop.classList.add('hidden');
                cropperModal.classList.add('hidden');
                document.body.style.overflow = '';
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            }

            carImageFileInput.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => openCropper(e.target.result);
                reader.readAsDataURL(file);
            });

            zoomRange.addEventListener('input', function() {
                if (!cropper) return;
                // Range 0..1 -> zoom ratio roughly 1x..4x relative to the initial fit
                cropper.zoomTo(1 + parseFloat(this.value) * 3);
            });

            cropperCancelBtn.addEventListener('click', function() {
                carImageFileInput.value = '';
                closeCropper();
            });

            cropperBackdrop.addEventListener('click', closeCropper);

            cropperConfirmBtn.addEventListener('click', function() {
                if (!cropper) return;
                // Preview only — the original file in carImageFileInput is what actually gets
                // uploaded on submit, untouched. This canvas is just to show the admin how the
                // photo will be framed inside the card image box on the Recommended Cars page.
                const canvas = cropper.getCroppedCanvas({
                    width: CROP_OUTPUT_WIDTH,
                    height: CROP_OUTPUT_HEIGHT,
                    imageSmoothingQuality: 'high'
                });
                imagePreviewImg.src = canvas.toDataURL('image/jpeg', 0.9);
                imagePreviewImg.classList.remove('hidden');
                if (imagePreviewIcon) imagePreviewIcon.classList.add('hidden');
                closeCropper();
            });
        </script>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-lg font-bold text-gray-800">Car Inventory <span class="text-gray-400 font-normal text-sm">(<?= $totalCars ?>)</span></h3>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="inventorySearch" placeholder="Search brand, model, fuel..." class="pl-9 pr-3 py-2 border rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64">
                </div>
                <select id="inventoryFuelFilter" class="px-3 py-2 border rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Fuel Types</option>
                    <?php foreach (['Petrol', 'Hybrid', 'EV', 'Diesel'] as $fuelOption): ?>
                        <option value="<?= h($fuelOption) ?>"><?= $fuelOption ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-gray-600">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Brand</th>
                        <th class="px-6 py-3">Model</th>
                        <th class="px-6 py-3">Body Type</th>
                        <th class="px-6 py-3">Engine</th>
                        <th class="px-6 py-3">Fuel</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Link</th>
                        <th class="px-6 py-3">Modified By</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (count($cars) > 0): ?>
                        <?php foreach ($cars as $row): ?>
                            <tr class="hover:bg-gray-50 transition duration-150"
                                data-search="<?= h(strtolower($row['brand'] . ' ' . $row['model'] . ' ' . $row['body_type'] . ' ' . $row['fuel'])) ?>"
                                data-fuel="<?= h($row['fuel']) ?>">
                                <td class="px-6 py-4 font-medium text-gray-900"><?= h($row['id']) ?></td>
                                <td class="px-6 py-4">
                                    <?php $rowImage = trim((string) ($row['image_path'] ?? '')); ?>
                                    <?php $rowImageFullPath = __DIR__ . '/' . ltrim($rowImage, '/'); ?>
                                    <?php if ($rowImage !== '' && file_exists($rowImageFullPath)): ?>
                                        <img src="<?= h($rowImage . '?v=' . filemtime($rowImageFullPath)) ?>" alt="" class="w-16 h-10 object-cover rounded-md border border-gray-200">
                                    <?php else: ?>
                                        <span class="flex items-center justify-center w-16 h-10 rounded-md border border-dashed border-gray-300 text-gray-300 text-xs">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4"><?= h($row['brand']) ?></td>
                                <td class="px-6 py-4"><?= h($row['model']) ?></td>
                                <td class="px-6 py-4"><?= h($row['body_type']) ?></td>
                                <td class="px-6 py-4">
                                    <?php if (strcasecmp($row['fuel'], 'EV') === 0 && $row['power_output_kw'] !== null): ?>
                                        <?= h($row['power_output_kw']) ?> kW
                                    <?php elseif ($row['engine_cc'] !== null && $row['engine_cc'] !== ''): ?>
                                        <?= h($row['engine_cc']) ?> CC
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4"><?= h($row['fuel']) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-800">RM <?= number_format($row['price'], 2) ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                        $href = $row['link'];
                                        if (!preg_match('#^(https?://)#i', $href)) {
                                            $href = 'https://' . ltrim($href, '/');
                                        }
                                    ?>
                                    <a href="<?= h($href) ?>" target="_blank" class="text-blue-600 hover:underline">View Link</a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-md text-xs font-medium">
                                        <?= h($row['modified_by'] ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <a href="admindashboard.php?edit=<?= h($row['id']) ?>#carFormSection" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                    <a href="admindashboard.php?delete=<?= h($row['id']) ?>" class="text-red-600 hover:text-red-800 font-medium" onclick="return confirm('Are you sure you want to delete this car?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="px-6 py-10 text-center text-gray-400 bg-gray-50">No cars found in the database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p id="inventoryNoMatches" class="hidden px-6 py-10 text-center text-gray-400">No cars match your search.</p>
        </div>
    </div>

</div>

<script>
    // ── Inventory search / fuel filter ──────────────────────────────────
    (function () {
        const searchInput = document.getElementById('inventorySearch');
        const fuelFilter = document.getElementById('inventoryFuelFilter');
        const rows = Array.from(document.querySelectorAll('table tbody tr[data-search]'));
        const noMatches = document.getElementById('inventoryNoMatches');
        if (!searchInput || !rows.length) return;

        function applyFilter() {
            const term = searchInput.value.trim().toLowerCase();
            const fuel = fuelFilter.value;
            let visibleCount = 0;
            rows.forEach(row => {
                const matchesTerm = !term || row.dataset.search.includes(term);
                const matchesFuel = !fuel || row.dataset.fuel === fuel;
                const show = matchesTerm && matchesFuel;
                row.classList.toggle('hidden', !show);
                if (show) visibleCount++;
            });
            noMatches.classList.toggle('hidden', visibleCount !== 0);
        }

        searchInput.addEventListener('input', applyFilter);
        fuelFilter.addEventListener('change', applyFilter);
    })();

    // If we're in edit mode (or a validation error kept us on the form), bring the form into view.
    <?php if ($update_mode || !empty($form_errors)): ?>
        document.getElementById('carFormSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    <?php endif; ?>
</script>

<script src="timezone-widget.js"></script>

</body>
</html>
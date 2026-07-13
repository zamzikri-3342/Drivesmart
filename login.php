<?php
require 'db.php';
require_once __DIR__ . '/config.php';
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $captchaToken = $_POST['h-captcha-response'] ?? '';

    if (empty($captchaToken)) {
        $error = 'Please complete the captcha.';
    } else {
        // Verify the token with hCaptcha's API
        $verify = curl_init('https://hcaptcha.com/siteverify');
        curl_setopt_array($verify, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'response' => $captchaToken,
                'secret'   => HCAPTCHA_SECRET_KEY,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]),
        ]);
        $verifyResult = json_decode(curl_exec($verify), true);
        curl_close($verify);

        if (empty($verifyResult['success'])) {
            $error = 'Captcha verification failed. Please try again.';
        } elseif (!empty($username) && !empty($password)) {
            $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['username'] = $admin['username'];
                header('Location: admindashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Please fill in all fields.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Admin Login</h2>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Username</label>
            <input type="text" name="username" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
            <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="mb-6 flex justify-center">
            <div class="h-captcha" data-sitekey="<?= HCAPTCHA_SITE_KEY ?>"></div>
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            Login
        </button>
    </form>
</div>

</body>
</html>
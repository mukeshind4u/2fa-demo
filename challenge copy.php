<?php
// challenge.php
session_start();
require 'vendor/autoload.php';
use OTPHP\TOTP;

$pdo = new PDO("mysql:host=localhost;dbname=auth_test", "root", "");
$error = "";

if (!isset($_SESSION['pending_user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $userId = $_SESSION['pending_user_id'];

    $stmt = $pdo->prepare("SELECT totp_secret FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $totp = TOTP::createFromSecret($user['totp_secret']);

    if ($totp->verify($code)) {
        $_SESSION['authenticated_user_id'] = $userId;
        unset($_SESSION['pending_user_id']); 
        
        // --- "REMEMBER THIS DEVICE" LOGIC ---
        if (isset($_POST['remember_device'])) {
            // 1. Create a random secure token
            $token = bin2hex(random_bytes(32));
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            
            // 2. Set expiration date (30 days from now)
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
            $cookieExpire = time() + (30 * 24 * 60 * 60);
            
            // 3. Save the hash in the database
            $stmt = $pdo->prepare("INSERT INTO trusted_devices (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $hashedToken, $expiresAt]);
            
            // 4. Give the browser the raw token as a cookie
            // Format: user_id:raw_token
            $cookieValue = $userId . ':' . $token;
            setcookie('trusted_device', $cookieValue, $cookieExpire, '/', '', false, true); // secure=false for localhost testing
        }
        // -----------------------------------------

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid Authenticator code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Challenge | Mashlelaka</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-2">Two-Factor Authentication</h2>
        <p class="text-sm text-gray-500 mb-6">Enter the 6-digit code from your authenticator app.</p>

        <?php if (!empty($error)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded relative text-left" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <input type="text" name="code" placeholder="000000" required autocomplete="one-time-code" autofocus
                    class="text-center text-2xl tracking-widest block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex items-center justify-start">
                <input id="remember_device" name="remember_device" type="checkbox" value="1" 
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="remember_device" class="ml-2 block text-sm text-gray-900">
                    Remember this device for 30 days
                </label>
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 transition duration-150">
                Verify Identity
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">Back to Login</a>
        </p>
    </div>

</body>
</html>
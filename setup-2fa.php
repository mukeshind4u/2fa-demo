<?php
// setup-2fa.php
session_start();
require 'vendor/autoload.php';
use OTPHP\TOTP;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$pdo = new PDO("mysql:host=localhost;dbname=auth_test", "root", "");
$error = "";

// Security check: Make sure they came from the registration page
if (!isset($_SESSION['setup_user_id'])) {
    header("Location: register.php");
    exit;
}

$userId = $_SESSION['setup_user_id'];

// 1. Keep the same secret on the screen even if they type the wrong code and refresh
if (!isset($_SESSION['temp_secret'])) {
    $totp = TOTP::generate();
    $_SESSION['temp_secret'] = $totp->getSecret();
}

$secret = $_SESSION['temp_secret'];
$totp = TOTP::createFromSecret($secret);

// 2. When they submit the form to prove they scanned it
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    
    if ($totp->verify($code)) {
        // SUCCESS! They proved they have it. Now we save it permanently.
        $stmt = $pdo->prepare("UPDATE users SET totp_secret = ? WHERE id = ?");
        $stmt->execute([$secret, $userId]);
        
        // Convert their setup session into a fully logged-in session
        $_SESSION['authenticated_user_id'] = $userId;
        unset($_SESSION['setup_user_id']);
        unset($_SESSION['temp_secret']);
        
        // Send them to the dashboard!
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid code. Please try again.";
    }
}

// 3. Draw the QR code
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userEmail = $stmt->fetchColumn();

$totp->setLabel($userEmail);
$totp->setIssuer('2FA Demo'); // Updated to your brand name!
$qrCode = new QrCode($totp->getProvisioningUri());
$writer = new PngWriter();
$result = $writer->write($qrCode);
$qrImageBase64 = base64_encode($result->getString());
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Your Account | 2FA Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen py-12">

    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8 text-center">
        
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Secure Your Account</h2>
        <p class="text-sm text-gray-500 mb-6">Scan this QR code with your authenticator app to link your device.</p>

        <?php if (!empty($error)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded relative text-left" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="flex justify-center mb-6">
            <div class="p-4 bg-white border-2 border-dashed border-gray-300 rounded-xl">
                <img src="data:image/png;base64,<?php echo $qrImageBase64; ?>" alt="2FA QR Code" class="w-48 h-48">
            </div>
        </div>

        <form method="POST" class="space-y-6">
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-2">Enter the 6-digit code to confirm</label>
                <input type="text" name="code" placeholder="000000" required autocomplete="one-time-code" autofocus
                    class="text-center text-2xl tracking-widest block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition duration-150">
                Complete Registration
            </button>
        </form>
        
    </div>

</body>
</html>
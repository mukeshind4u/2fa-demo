<?php
// login.php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=auth_test", "root", "");
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        
        // --- CHECK FOR TRUSTED DEVICE COOKIE ---
        $isDeviceTrusted = false;
        
        if (isset($_COOKIE['trusted_device'])) {
            list($cookieUserId, $cookieToken) = explode(':', $_COOKIE['trusted_device']);
            
            // Make sure the cookie belongs to the person logging in
            if ($cookieUserId == $user['id']) {
                $stmt = $pdo->prepare("SELECT * FROM trusted_devices WHERE user_id = ? AND expires_at > NOW()");
                $stmt->execute([$user['id']]);
                $trustedDevices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($trustedDevices as $device) {
                    if (password_verify($cookieToken, $device['token_hash'])) {
                        $isDeviceTrusted = true;
                        break;
                    }
                }
            }
        }
        // ---------------------------------------------

        // If trusted, go straight to the dashboard!
        if ($isDeviceTrusted) {
            $_SESSION['authenticated_user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            // If not trusted, send them to the 2FA waiting room
            $_SESSION['pending_user_id'] = $user['id'];
            header("Location: challenge.php");
            exit;
        }

    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | 2FA Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">2FA Demo</h1>
            <p class="text-gray-500 mt-2">Sign in to your account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" name="email" required 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                Sign In
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account? 
            <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">Register here</a>
        </p>
    </div>

</body>
</html>
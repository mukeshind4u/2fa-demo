<?php
// register.php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=auth_test", "root", "");
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 1. Insert the new user with a BLANK secret for now
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, totp_secret) VALUES (?, ?, '')");
    
    try {
        $stmt->execute([$email, $hashedPassword]);
        $newUserId = $pdo->lastInsertId();
        
        // 2. Put their new ID into a setup session
        $_SESSION['setup_user_id'] = $newUserId;
        
        // 3. Send them to configure their 2FA
        header("Location: setup-2fa.php");
        exit;
    } catch (PDOException $e) {
        $error = "That email is already registered!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | 2FA Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">2FA Demo</h1>
            <p class="text-gray-500 mt-2">Create a new account</p>
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
                Register
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account? 
            <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">Log in</a>
        </p>
    </div>

</body>
</html>
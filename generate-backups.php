<?php
// generate-backups.php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=auth_test", "root", "");

// Only logged-in users can generate backup codes!
if (!isset($_SESSION['authenticated_user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['authenticated_user_id'];
$generatedCodes = [];

// Auto-create the backup codes table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS backup_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code_hash VARCHAR(255) NOT NULL
)");

// If the user clicks the "Generate Codes" button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    
    // 1. Delete any old backup codes this user might have
    $stmt = $pdo->prepare("DELETE FROM backup_codes WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 2. Generate 10 brand new secure codes
    $insertStmt = $pdo->prepare("INSERT INTO backup_codes (user_id, code_hash) VALUES (?, ?)");
    
    for ($i = 0; $i < 10; $i++) {
        // Create an 8-character random string (e.g., 'f4a2b9d1')
        $rawCode = bin2hex(random_bytes(4));
        $generatedCodes[] = $rawCode;
        
        // Hash it before saving to the database
        $hashedCode = password_hash($rawCode, PASSWORD_DEFAULT);
        $insertStmt->execute([$userId, $hashedCode]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Codes | Mashlelaka</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-3xl mx-auto">
        
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Recovery Codes</h1>
                <p class="text-sm text-gray-600">Generate emergency backup codes for your account.</p>
            </div>
            <a href="dashboard.php" class="text-sm font-medium text-blue-600 hover:text-blue-800">&larr; Back to Dashboard</a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            
            <div class="flex items-start mb-6">
                <div class="flex-shrink-0 mt-1">
                    <svg class="h-6 w-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Important Warning</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        These codes are the <strong>only</strong> way to access your account if you lose your device. 
                        Each code can only be used once. Keep them in a safe place, like a password manager or a printed piece of paper.
                    </p>
                </div>
            </div>

            <?php if (empty($generatedCodes)): ?>
                <form method="POST" class="mt-6 border-t border-gray-100 pt-6 text-center">
                    <p class="text-sm text-gray-500 mb-4">Generating new codes will invalidate any old codes you previously saved.</p>
                    <button type="submit" name="generate" 
                        class="inline-flex justify-center py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition duration-150">
                        Generate 10 New Codes
                    </button>
                </form>

            <?php else: ?>
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-6 mb-6">
                        <div class="grid grid-cols-2 gap-4 text-center font-mono text-lg tracking-widest text-gray-900">
                            <?php foreach ($generatedCodes as $code): ?>
                                <div class="bg-white py-2 border border-gray-200 rounded shadow-sm"><?php echo $code; ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-sm text-red-600 font-medium mb-4">You will only see these codes once. Please save them now.</p>
                        <a href="dashboard.php" class="inline-flex justify-center py-2 px-6 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition duration-150">
                            I have saved my codes
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
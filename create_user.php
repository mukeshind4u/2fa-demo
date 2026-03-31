<?php
require 'vendor/autoload.php';
use OTPHP\TOTP;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Connect to the database
$pdo = new PDO("mysql:host=localhost;dbname=auth_test", "root", "");

// --- NEW: Automatically create the users table if it doesn't exist ---
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    totp_secret VARCHAR(255) NOT NULL
)");
// ----------------------------------------------------------------------

// 1. The account details
$email = "test@example.com";
$password = "password123";
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 2. Generate a brand new TOTP secret for this user
$totp = TOTP::generate();
$secret = $totp->getSecret();

// 3. Insert the user into the database
// (We use IGNORE so if you run this twice, it doesn't duplicate the user)
$stmt = $pdo->prepare("INSERT IGNORE INTO users (email, password_hash, totp_secret) VALUES (?, ?, ?)");
$stmt->execute([$email, $hashedPassword, $secret]);

// 4. Set up the QR Code
$totp->setLabel($email);
$totp->setIssuer('My 2FA App');
$qrCode = new QrCode($totp->getProvisioningUri());
$writer = new PngWriter();
$result = $writer->write($qrCode);
$qrImageBase64 = base64_encode($result->getString());
?>

<!DOCTYPE html>
<html>
<body>
    <div style="font-family: Arial; padding: 20px;">
        <h2 style="color: green;">✅ Table and Test User Created!</h2>
        <p>Your database now has the table and the data.</p>
        
        <table border="1" cellpadding="10" style="border-collapse: collapse;">
            <tr><td><b>Email:</b></td><td>test@example.com</td></tr>
            <tr><td><b>Password:</b></td><td>password123</td></tr>
        </table>

        <h3>Crucial Step: Scan this QR Code NOW</h3>
        <p>Before you click the login link, open Google or Microsoft Authenticator and scan this QR code so your phone can generate the right 6-digit codes.</p>
        
        <img src="data:image/png;base64,<?php echo $qrImageBase64; ?>" style="width: 200px; height: 200px; border: 1px solid #ccc;">
        <br><br>
        
        <a href="login.php"><button style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Go to Login Page</button></a>
    </div>
</body>
</html>
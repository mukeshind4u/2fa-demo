<?php
// dashboard.php
session_start();

// If they don't have the final authenticated session, kick them out
if (!isset($_SESSION['authenticated_user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | 2FA Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-gray-900">2FA Demo
</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">Welcome to your dashboard</span>
                    <a href="logout.php" 
                        class="text-sm font-medium text-red-600 hover:text-red-800 border border-red-200 bg-red-50 py-1.5 px-3 rounded transition duration-150">
                        Log out
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">🎉 Welcome to the Secure Dashboard!</h1>
            <p class="mt-2 text-sm text-gray-600">You have successfully passed both your password and your two-factor authentication check.</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Security Overview</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-green-50 border border-green-100 rounded-lg p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="h-3 w-3 rounded-full bg-green-500 inline-block"></span>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Authentication Status</h3>
                                <p class="text-xs text-green-600 mt-1">2FA is currently active and protecting your account.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex flex-col items-center justify-center text-center">
                        <svg class="h-6 w-6 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <h3 class="text-sm font-medium text-gray-900">Recovery Codes</h3>
                        <p class="text-xs text-gray-500 mt-1">Generate emergency codes.</p>
                        <button class="mt-3 text-xs text-blue-600 font-medium hover:text-blue-800">Configure &rarr;</button>
                    </div>
                </div>

            </div>
        </div>

    </main>

</body>
</html>
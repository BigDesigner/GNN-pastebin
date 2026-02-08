<?php
// Start session
session_start();

// Flood protection settings
define('FLOOD_INTERVAL', 60); // 1 minute
define('MAX_REQUESTS_PER_INTERVAL', 5); // Maximum 5 requests per minute
define('BLACKLIST_THRESHOLD', 3); // Number of flood attempts before blacklisting
define('BLACKLIST_DURATION', 300); // 5 minutes blacklisted

// Flood protection function
function floodProtection()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $floodKey = 'flood_' . $ip;

    // Get the current flood data
    $floodData = $_SESSION[$floodKey] ?? [];

    // Add the current request time to the flood data
    $floodData[] = time();

    // Remove old entries
    $floodData = array_filter($floodData, function ($timestamp) {
        return $timestamp > (time() - FLOOD_INTERVAL);
    });

    // Save the updated flood data
    $_SESSION[$floodKey] = $floodData;

    // Check if the number of requests exceeds the limit
    if (count($floodData) > MAX_REQUESTS_PER_INTERVAL) {
        // Increment flood attempt count
        $floodAttempts = $_SESSION['flood_attempts_' . $ip] ?? 0;
        $floodAttempts++;
        $_SESSION['flood_attempts_' . $ip] = $floodAttempts;

        // Check if the IP should be blacklisted
        if ($floodAttempts >= BLACKLIST_THRESHOLD) {
            blacklistIP($ip);
            return false;
        }

        return false;
    }

    return true;
}

// Function to blacklist an IP
function blacklistIP($ip)
{
    $blacklistFile = __DIR__ . '/blacklist.txt';
    $blacklistData = file_exists($blacklistFile) ? file($blacklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    // Check if the IP is already blacklisted
    if (!in_array($ip, $blacklistData)) {
        $blacklistData[] = $ip;
        file_put_contents($blacklistFile, implode("\n", $blacklistData));
    }
}

// Function to check if an IP is blacklisted
function isBlacklisted($ip)
{
    $blacklistFile = __DIR__ . '/blacklist.txt';
    $blacklistData = file_exists($blacklistFile) ? file($blacklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    return in_array($ip, $blacklistData);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'GNN Pastebin'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/dark.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
</head>

<body class="bg-gray-900 text-white">
    <nav class="bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50 border-b border-white/5">
        <div class="container mx-auto p-4 flex items-center justify-between">
            <a href="index.php" class="flex items-center space-x-2 group">
                <img src="logo/gnnpaste.svg" class="h-8 amber-svg" alt="GNNpaste" />
                <span
                    class="text-2xl font-extrabold tracking-tight text-amber-500 transition-all group-hover:tracking-wider">GNNpaste</span>
            </a>
            <button data-collapse-toggle="navbar-default" type="button"
                class="inline-flex items-center p-2 w-10 h-10 justify-center text-gray-400 rounded-xl md:hidden hover:bg-white/5 focus:outline-none transition-all"
                aria-controls="navbar-default" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
            <div class="hidden w-full md:block md:w-auto" id="navbar-default">
                <ul
                    class="font-medium flex flex-col p-4 md:p-0 mt-4 md:flex-row md:space-x-8 md:mt-0 md:border-0 md:bg-transparent">
                    <li>
                        <a href="index.php"
                            class="block py-2 px-3 text-amber-500 hover:text-amber-400 font-semibold transition-all hover:scale-105"
                            aria-current="page">Home</a>
                    </li>
                    <li>
                        <a href="admin.php"
                            class="block py-2 px-3 text-gray-400 hover:text-amber-500 font-semibold transition-all hover:scale-105">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mx-auto mt-10 px-4 pb-20">
        <div class="glass-panel p-6 md:p-8 rounded-2xl w-full">
            <!-- Content will go here -->
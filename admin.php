<?php
session_start();

// Include the config file
require 'config.php';

// Check if the user is already logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

        // Verify reCAPTCHA response
        $recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET_KEY . "&response={$recaptchaResponse}";
        $recaptcha = file_get_contents($recaptchaUrl);
        $recaptcha = json_decode($recaptcha);

        if ($recaptcha->success && $username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['admin_logged_in'] = true;
        } else {
            $error = "Invalid username, password, or reCAPTCHA.";
        }
    }

    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $title = "Admin Login";
        include 'header.php';
        echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>";
        echo "
            <form action='' method='post' class='w-full max-w-sm mx-auto'>
                <div class='mb-4'>
                    <input type='text' id='username' name='username' class='form-input p-2 bg-gray-700 text-white border-gray-600 rounded-md w-full' placeholder='Username' required>
                </div>
                <div class='mb-4'>
                    <input type='password' id='password' name='password' class='form-input p-2 bg-gray-700 text-white border-gray-600 rounded-md w-full' placeholder='Password' required>
                </div>
                <div class='mb-4'>
                    <div class='g-recaptcha' data-sitekey='" . RECAPTCHA_SITE_KEY . "' data-theme='dark'></div>
                </div>
                <button type='submit' class='btn btn-primary bg-amber-500 hover:bg-amber-700 text-white px-4 py-2 rounded-md w-full'>Login</button>
            </form>
            " . (isset($error) ? "<p class='text-red-500 mt-4'>$error</p>" : "") . "
        ";
        include 'footer.php';
        exit;
    }
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Function to delete expired files
function deleteExpiredFiles()
{
    $directory = __DIR__ . '/file/';
    $files = glob($directory . '*.meta'); // List .meta files

    foreach ($files as $metaFile) {
        $metaData = json_decode(file_get_contents($metaFile), true);

        // Check the expiration date
        $expireAt = $metaData['expire_at'] ?? null;
        if ($expireAt && time() > $expireAt) {
            // Delete the related .data and .meta files
            $baseName = basename($metaFile, '.meta');
            $dataFile = $directory . $baseName . '.data';

            if (file_exists($dataFile)) {
                if (!unlink($dataFile)) {
                    error_log("Failed to delete file: $dataFile");
                }
            }
            if (!unlink($metaFile)) {
                error_log("Failed to delete file: $metaFile");
            }
        }
    }
}

// Delete expired files if the button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_expired'])) {
    deleteExpiredFiles();
}

// Delete individual file if the delete button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $fileName = $_POST['delete_file'];
    $filePath = __DIR__ . "/file/$fileName.data";
    $metaPath = __DIR__ . "/file/$fileName.meta";

    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            error_log("Failed to delete file: $filePath");
        }
    }
    if (file_exists($metaPath)) {
        if (!unlink($metaPath)) {
            error_log("Failed to delete file: $metaPath");
        }
    }
}

// List all files in the file directory
$directory = __DIR__ . '/file/';
$files = glob($directory . '*.data');

$title = "Admin Panel";
include 'header.php';
echo "
    <div class='flex justify-end space-x-4 mb-4'>
        <form action='' method='post'>
            <button type='submit' name='delete_expired' class='btn btn-danger bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md'>Delete Expired Files</button>
        </form>
        <a href='?logout' class='btn btn-secondary bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md'>Logout</a>
    </div>
    <h3 class='text-xl font-bold mb-4'>Uploaded Files</h3>
    <table class='min-w-full bg-gray-800 rounded-lg shadow-lg'>
        <thead>
            <tr>
                <th class='px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider'>File Name</th>
                <th class='px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider'>Uploaded At</th>
                <th class='px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider'>Actions</th>
            </tr>
        </thead>
        <tbody class='bg-gray-700 divide-y divide-gray-600'>
";
foreach ($files as $file) {
    $baseName = basename($file, '.data');
    $metaFile = $directory . $baseName . '.meta';
    $metaData = json_decode(file_get_contents($metaFile), true);
    $createdAt = date('Y-m-d H:i:s', $metaData['created_at']);
    echo "
        <tr>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-300'><a href='index.php?file=$baseName' target='_blank' class='text-amber-500 hover:underline'>$baseName</a></td>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-300'>$createdAt</td>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-300'>
                <form action='' method='post' data-delete-file>
                    <input type='hidden' name='delete_file' value='$baseName'>
                    <button type='submit' class='btn btn-danger bg-amber-800 hover:bg-red-600 text-white px-4 py-2 rounded-md'>Delete</button>
                </form>
            </td>
        </tr>
    ";
}
echo "
        </tbody>
    </table>
";
include 'footer.php';
?>
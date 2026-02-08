<?php
// Start session
session_start();

// Generate a random file name using UUID
function generateRandomName()
{
    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

// AES encryption and decryption functions
function encryptContent($content, $key)
{
    return openssl_encrypt($content, 'AES-128-CTR', $key, 0, AES_IV); // IV must be a fixed length
}

function decryptContent($encryptedContent, $key)
{
    return openssl_decrypt($encryptedContent, 'AES-128-CTR', $key, 0, AES_IV);
}

// Include configuration
require_once 'config.php';

// Maximum file size limit
define('MAX_FILE_SIZE', 64 * 1024); // 128 KB

// CSRF Token Generation
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token Validation
function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Save operation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $content = $_POST['content'] ?? '';
    $expireOption = $_POST['expire'] ?? 'never'; // User's selected duration
    $randomName = generateRandomName();

    // Check file size
    if (strlen($content) > MAX_FILE_SIZE) {
        die("File size exceeds the limit.");
    }

    // Encrypt and save the content in our format
    $encryptedContent = encryptContent($content, ENCRYPTION_KEY);
    $filePath = __DIR__ . "/file/$randomName.data";
    file_put_contents($filePath, $encryptedContent);

    // Calculate the expiration time
    $expireTime = null;
    switch ($expireOption) {
        case '1day':
            $expireTime = strtotime('+1 day');
            break;
        case '7days':
            $expireTime = strtotime('+7 days');
            break;
        case '1month':
            $expireTime = strtotime('+1 month');
            break;
        case '6months':
            $expireTime = strtotime('+6 months');
            break;
        case 'never':
        default:
            $expireTime = null; // Never expires
    }

    // Create metadata file
    $metaPath = __DIR__ . "/file/$randomName.meta";
    $metaData = [
        'created_at' => time(),
        'expire_at' => $expireTime,
    ];
    file_put_contents($metaPath, json_encode($metaData));

    // Provide the user with a unique link
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $currentPath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // Directory where the script is located
    $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $currentPath . "/?file=$randomName";
    $title = "Text Saved";
    include 'header.php';
    echo "
            <h2 class='text-2xl font-bold mb-4'>Your Text Has Been Saved!</h2>
            <p class='mb-4'>You can copy the link below:</p>
            <div class='flex items-center space-x-2'>
                <input type='text' class='form-input bg-gray-700 text-white border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500 block p-3 w-full' value='$url' id='generatedLink' readonly>
                <button class='btn btn-primary bg-amber-500 text-white px-4 py-3 rounded-md hover:bg-amber-700' onclick='copyLink()'>Copy</button>
            </div>
            <a href='$url' class='btn btn-success bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-md mt-4 inline-block w-full text-center'>View Text</a>
    ";
    include 'footer.php';
    exit;
}

// View operation
if (isset($_GET['file'])) {
    $fileName = preg_replace('/[^a-zA-Z0-9-]/', '', $_GET['file']);
    $filePath = __DIR__ . "/file/$fileName.data";

    if (file_exists($filePath)) {
        $encryptedContent = file_get_contents($filePath);
        $content = decryptContent($encryptedContent, ENCRYPTION_KEY);
        $rawContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        $title = "View Text";
        include 'header.php';
        echo "
            <h2 class='text-2xl font-bold mb-4'>View Text</h2>
            <div class='relative'>
                <pre><code class='php' id='codeContent'>$rawContent</code></pre>
                <button class='btn btn-primary bg-amber-500 text-white px-2 py-2 rounded-md absolute top-2 right-2 hover:bg-amber-700' onclick='copyCode()'>Copy</button>
            </div>
        ";
        include 'footer.php';
    } else {
        echo "This file does not exist.";
    }
    exit;
}
?>
<?php
$title = "GNN Pastebin";
include 'header.php';
?>
<form action="" method="post">
    <div class="mb-4">
        <label for="content" class="block mb-2 text-lg font-bold font-medium text-gray-300">Pastebin:</label>
        <textarea id="content" name="content"
            class="form-textarea bg-gray-700 text-white border-gray-600 rounded-md w-full" rows="8" required></textarea>
    </div>
    <div class="mb-4">
        <label for="expire" class="block mb-2 text-lg font-bold font-medium text-gray-300">Expiration Time:</label>
        <select id="expire" name="expire"
            class="form-select bg-gray-700 text-white border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500 block p-3 w-full">
            <option value="1day">1 Day</option>
            <option value="7days">7 Days</option>
            <option value="1month">1 Month</option>
            <option value="6months">6 Months</option>
            <option value="never">Never</option>
        </select>
    </div>
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <button type="submit"
        class="btn btn-primary bg-amber-500 hover:bg-amber-700 text-white px-4 py-2 rounded-md w-full">Save</button>
</form>
<?php
include 'footer.php';
?>
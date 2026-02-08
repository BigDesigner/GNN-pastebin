<?php
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

echo "Cleanup completed.";
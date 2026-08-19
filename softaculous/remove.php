<?php
/**
 * Softaculous Removal Script for Vertex CMS
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

chdir(dirname(__DIR__));

// Remove .env file (contains DB credentials)
if (file_exists('.env')) {
    unlink('.env');
}

// Remove installed lock
if (file_exists('storage/installed.lock')) {
    unlink('storage/installed.lock');
}

// Clear cache directories
$cacheDirs = [
    'bootstrap/cache',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
];

foreach ($cacheDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

echo "✅ Vertex CMS removed successfully!\n";
echo "Database tables were NOT dropped. Please remove manually if needed.\n";

exit(0);

<?php
/**
 * Softaculous Upgrade Script for Vertex CMS
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

chdir(dirname(__DIR__));

echo "Starting Vertex CMS upgrade...\n\n";

// Create backup before upgrade
echo "Creating backup...\n";
exec('php artisan backup:create --database 2>&1', $output, $return);
if ($return !== 0) {
    echo "WARNING: Backup failed, continuing anyway...\n";
}

// Install new dependencies
echo "Installing dependencies...\n";
exec('composer install --no-dev --optimize-autoloader 2>&1', $output, $return);
if ($return !== 0) {
    echo "ERROR: Composer install failed\n";
    echo implode("\n", $output);
    exit(1);
}

// Clear configuration cache
echo "Clearing cache...\n";
exec('php artisan config:clear 2>&1');
exec('php artisan route:clear 2>&1');
exec('php artisan view:clear 2>&1');

// Run migrations
echo "Running database migrations...\n";
exec('php artisan migrate --force 2>&1', $output, $return);
if ($return !== 0) {
    echo "ERROR: Migrations failed\n";
    echo implode("\n", $output);
    // Rollback
    exec('php artisan migrate:rollback --force 2>&1');
    exit(1);
}

// Re-cache configuration
echo "Optimizing application...\n";
exec('php artisan config:cache 2>&1');
exec('php artisan route:cache 2>&1');
exec('php artisan view:cache 2>&1');

echo "\n✅ Upgrade completed successfully!\n";

exit(0);

<?php
/**
 * Softaculous Installation Script for Vertex CMS
 */

// Disable error display for security
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Get installation data from Softaculous
$softdbuser = $_POST['softdbuser'] ?? '';
$softdbpass = $_POST['softdbpass'] ?? '';
$softdbhost = $_POST['softdbhost'] ?? 'localhost';
$softdbname = $_POST['softdbname'] ?? '';
$siteurl = $_POST['siteurl'] ?? '';
$adminemail = $_POST['adminemail'] ?? '';
$adminpass = $_POST['adminpass'] ?? '';
$adminname = $_POST['adminname'] ?? 'Admin';

// Validate required fields
$required = ['softdbuser', 'softdbpass', 'softdbname', 'siteurl', 'adminemail', 'adminpass'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo "ERROR: Missing required field: $field";
        exit(1);
    }
}

// Change to installation directory
chdir(dirname(__DIR__));

// Create .env file
$envContent = <<<ENV
APP_NAME="Vertex CMS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL={$siteurl}

DB_CONNECTION=mysql
DB_HOST={$softdbhost}
DB_PORT=3306
DB_DATABASE={$softdbname}
DB_USERNAME={$softdbuser}
DB_PASSWORD={$softdbpass}

ADMIN_EMAIL={$adminemail}
ADMIN_NAME={$adminname}
ENV;

file_put_contents('.env', $envContent);

// Set permissions
chmod('storage', 0777);
chmod('bootstrap/cache', 0777);
chmod('.env', 0644);

// Install dependencies
echo "Installing dependencies...\n";
exec('composer install --no-dev --optimize-autoloader 2>&1', $output, $return);
if ($return !== 0) {
    echo "ERROR: Composer install failed\n";
    echo implode("\n", $output);
    exit(1);
}

// Generate application key
echo "Generating application key...\n";
exec('php artisan key:generate 2>&1', $output, $return);
if ($return !== 0) {
    echo "ERROR: Key generation failed\n";
    exit(1);
}

// Run migrations
echo "Running database migrations...\n";
exec('php artisan migrate --force 2>&1', $output, $return);
if ($return !== 0) {
    echo "ERROR: Migrations failed\n";
    echo implode("\n", $output);
    exit(1);
}

// Create admin user
echo "Creating admin user...\n";
$createAdminCmd = "php artisan tinker --execute=\"App\\Models\\User::create(['name' => '{$adminname}', 'email' => '{$adminemail}', 'password' => bcrypt('{$adminpass}'), 'role' => 'admin']);\" 2>&1";
exec($createAdminCmd, $output, $return);

// Create installed lock file
file_put_contents('storage/installed.lock', date('Y-m-d H:i:s'));

// Clear and cache configuration
exec('php artisan config:cache 2>&1');
exec('php artisan route:cache 2>&1');
exec('php artisan view:cache 2>&1');

echo "\n✅ Installation completed successfully!\n";
echo "Admin URL: {$siteurl}/admin\n";
echo "Email: {$adminemail}\n";

exit(0);

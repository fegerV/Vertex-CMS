#!/bin/bash

# Vertex CMS - Build Release Script
# Creates a clean production archive ready for hosting deployment

set -e

# Configuration
ARCHIVE_NAME="vertex-cms.zip"
BUILD_DIR="build"
PROJECT_NAME="vertex-cms"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}  Vertex CMS - Build Release Script  ${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""

# Check if running in project root
if [ ! -f "composer.json" ] || [ ! -f "artisan" ]; then
    echo -e "${RED}Error: This script must be run from the project root directory${NC}"
    exit 1
fi

# Clean up previous build
echo -e "${YELLOW}Cleaning up previous build...${NC}"
rm -rf "$BUILD_DIR"
rm -f "$ARCHIVE_NAME"
mkdir -p "$BUILD_DIR/$PROJECT_NAME"

# Copy necessary files and directories
echo -e "${YELLOW}Copying production files...${NC}"

# Core Laravel files
cp composer.json "$BUILD_DIR/$PROJECT_NAME/"
cp composer.lock "$BUILD_DIR/$PROJECT_NAME/"
cp artisan "$BUILD_DIR/$PROJECT_NAME/"
cp package.json "$BUILD_DIR/$PROJECT_NAME/"
cp vite.config.js "$BUILD_DIR/$PROJECT_NAME/"
cp tailwind.config.js "$BUILD_DIR/$PROJECT_NAME/"
cp postcss.config.js "$BUILD_DIR/$PROJECT_NAME/"

# Directories to copy
directories=(
    "app"
    "bootstrap"
    "config"
    "database"
    "lang"
    "modules"
    "public"
    "resources"
    "routes"
    "themes"
)

for dir in "${directories[@]}"; do
    if [ -d "$dir" ]; then
        cp -r "$dir" "$BUILD_DIR/$PROJECT_NAME/"
        echo "  ✓ Copied $dir/"
    fi
done

# Copy environment example
cp .env.example "$BUILD_DIR/$PROJECT_NAME/.env.example"

# Copy documentation
cp README.md "$BUILD_DIR/$PROJECT_NAME/"
cp INSTALL.md "$BUILD_DIR/$PROJECT_NAME/"

# Create .gitignore for the build
cat > "$BUILD_DIR/$PROJECT_NAME/.gitignore" << 'EOF'
.env
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
bootstrap/cache/*
node_modules/
vendor/
*.log
.DS_Store
Thumbs.db
EOF

# Create installation script
cat > "$BUILD_DIR/$PROJECT_NAME/install.sh" << 'INSTALL_SCRIPT'
#!/bin/bash

# Vertex CMS Installation Script
# Run this script on your hosting server after uploading the archive

set -e

echo "======================================"
echo "  Vertex CMS Installation Script"
echo "======================================"
echo ""

# Check PHP version
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
REQUIRED_VERSION="8.1"

echo "Checking PHP version..."
if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PHP_VERSION" | sort -V | head -n 1)" != "$REQUIRED_VERSION" ]; then
    echo "Error: PHP version $REQUIRED_VERSION or higher is required. Found: $PHP_VERSION"
    exit 1
fi
echo "✓ PHP version: $PHP_VERSION"

# Check required PHP extensions
echo "Checking PHP extensions..."
REQUIRED_EXTENSIONS=("mbstring" "xml" "curl" "mysql" "gd" "json" "zip")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        echo "  ✓ $ext"
    else
        echo "  ✗ $ext (missing)"
        MISSING_EXT=true
    fi
done

if [ "$MISSING_EXT" = true ]; then
    echo "Error: Some required PHP extensions are missing"
    exit 1
fi

# Check if composer is available
if ! command -v composer &> /dev/null; then
    echo "Composer not found. Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install dependencies
echo ""
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node.js dependencies (if npm is available)
if command -v npm &> /dev/null; then
    echo ""
    echo "Installing frontend dependencies..."
    npm ci --production
    echo "Building frontend assets..."
    npm run build
else
    echo "npm not found. Skipping frontend build."
    echo "You can build assets later with: npm install && npm run build"
fi

# Set up permissions
echo ""
echo "Setting up directory permissions..."
chmod -R 775 storage bootstrap/cache
find storage -type f -exec chmod 664 {} \;
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

# Create .env file
echo ""
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    
    # Generate application key
    echo "Generating application key..."
    php artisan key:generate
else
    echo ".env file already exists. Skipping."
fi

# Run database migrations
echo ""
read -p "Do you want to run database migrations? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Enter database name: " DB_NAME
    read -p "Enter database user: " DB_USER
    read -sp "Enter database password: " DB_PASS
    echo
    read -p "Enter database host (default: localhost): " DB_HOST
    DB_HOST=${DB_HOST:-localhost}
    
    # Update .env with database credentials
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
    sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    
    echo "Running migrations..."
    php artisan migrate --force
fi

# Clear caches
echo ""
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Final message
echo ""
echo "======================================"
echo "  Installation Complete!"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Configure your web server (Apache/Nginx) to point to the 'public' directory"
echo "2. Ensure mod_rewrite is enabled (for Apache)"
echo "3. Visit your website in a browser to complete setup"
echo ""
echo "For more information, see INSTALL.md"
echo ""
INSTALL_SCRIPT

chmod +x "$BUILD_DIR/$PROJECT_NAME/install.sh"

# Create INSTALL.txt with quick instructions
cat > "$BUILD_DIR/$PROJECT_NAME/INSTALLATION.txt" << 'INSTALL_TXT'
VERTEX CMS - QUICK INSTALLATION GUIDE
=====================================

Requirements:
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Required PHP extensions: mbstring, xml, curl, mysql, gd, json, zip
- Composer
- Node.js and npm (optional, for building assets)

Installation Steps:
-------------------

1. Upload the vertex-cms.zip archive to your hosting server

2. Extract the archive:
   unzip vertex-cms.zip
   cd vertex-cms

3. Run the installation script:
   chmod +x install.sh
   ./install.sh

   Or manually:
   
   a) Install PHP dependencies:
      composer install --no-dev --optimize-autoloader
   
   b) Copy .env.example to .env:
      cp .env.example .env
   
   c) Generate application key:
      php artisan key:generate
   
   d) Configure your database in .env file:
      DB_DATABASE=your_database_name
      DB_USERNAME=your_username
      DB_PASSWORD=your_password
      DB_HOST=localhost
   
   e) Run migrations:
      php artisan migrate
   
   f) Set permissions:
      chmod -R 775 storage bootstrap/cache
   
   g) Build frontend assets (optional):
      npm install
      npm run build

4. Configure your web server:
   
   Apache: Point document root to the 'public' directory
   Nginx: See docs/nginx.conf for configuration example

5. Clear caches:
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear

6. Visit your website in a browser

Post-Installation:
------------------
- Create an admin account through the web interface
- Configure site settings in the admin panel
- Set up cron jobs for scheduled tasks:
  * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

For detailed installation instructions, see INSTALL.md
For troubleshooting, check docs/ directory

Support:
--------
For support and documentation, visit the project repository.
INSTALL_TXT

# Create exclusion list for zip
cat > "$BUILD_DIR/exclude.txt" << 'EOF'
.git
.github
node_modules
vendor
storage/logs
storage/framework/cache
storage/framework/sessions
storage/framework/views
bootstrap/cache
.env
*.log
.DS_Store
Thumbs.db
.vscode
.idea
tests
.php_cs.cache
npm-debug.log*
yarn-debug.log*
yarn-error.log*
coverage
htmlcov
.build
build.sh
exclude.txt
EOF

# Remove development-only files from build
echo -e "${YELLOW}Removing development files...${NC}"
rm -rf "$BUILD_DIR/$PROJECT_NAME/tests"
rm -rf "$BUILD_DIR/$PROJECT_NAME/node_modules"

# Remove developer documentation that's not needed for end users
rm -f "$BUILD_DIR/$PROJECT_NAME/DEVELOPMENT_PLAN.md"
rm -f "$BUILD_DIR/$PROJECT_NAME/CHANGES.md"
rm -f "$BUILD_DIR/$PROJECT_NAME/pr_status.txt"
rm -f "$BUILD_DIR/$PROJECT_NAME/blocks_types.txt"
rm -f "$BUILD_DIR/$PROJECT_NAME/cms-admin-documentation.md"
rm -f "$BUILD_DIR/$PROJECT_NAME/HEALTH_CHECKS.md"
rm -f "$BUILD_DIR/$PROJECT_NAME/ADMIN_AUDIT_REPORT.md"
rm -f "$BUILD_DIR/$PROJECT_NAME/IMPLEMENTATION_SUMMARY.md"
rm -f "$BUILD_DIR/$PROJECT_NAME/README_FINAL.md"
rm -f "$BUILD_DIR/$PROJECT_NAME/README.md.bak"
rm -f "$BUILD_DIR/$PROJECT_NAME/CLAUDE.md"
rm -f "$BUILD_DIR/$PROJECT_NAME/AGENTS.md"

# Create the zip archive
echo -e "${YELLOW}Creating archive: $ARCHIVE_NAME${NC}"
cd "$BUILD_DIR"
zip -r "../$ARCHIVE_NAME" "$PROJECT_NAME" -x "*.git*" -x "node_modules/*" -x "vendor/*" -x "storage/logs/*" -x "storage/framework/cache/*" -x "storage/framework/sessions/*" -x "storage/framework/views/*" -x "bootstrap/cache/*" -x "*.log" -x ".DS_Store" -x "Thumbs.db" -x ".vscode/*" -x ".idea/*" -x "tests/*"
cd ..

# Calculate archive size
ARCHIVE_SIZE=$(du -h "$ARCHIVE_NAME" | cut -f1)

# Display success message
echo ""
echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}  Build Complete!                    ${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""
echo "Archive: $ARCHIVE_NAME"
echo "Size: $ARCHIVE_SIZE"
echo "Location: $(pwd)/$ARCHIVE_NAME"
echo ""
echo "Contents:"
unzip -l "$ARCHIVE_NAME" | head -20
echo "..."
echo ""
echo -e "${YELLOW}To deploy:${NC}"
echo "1. Upload $ARCHIVE_NAME to your hosting server"
echo "2. Extract: unzip $ARCHIVE_NAME"
echo "3. Navigate to the extracted directory"
echo "4. Run: ./install.sh"
echo ""
echo -e "${GREEN}Ready for deployment!${NC}"

#!/bin/bash
#
# Build script for RationalCleanup WordPress plugin
# Creates a clean distribution package for WordPress.org submission
#

set -e

PLUGIN_SLUG="rationalcleanup"
VERSION="1.0.0"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

echo "Building ${PLUGIN_SLUG} v${VERSION}..."

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Clean previous build
rm -rf dist
mkdir -p "dist/${PLUGIN_SLUG}"

# Copy production files
echo "Copying production files..."
cp -r assets "dist/${PLUGIN_SLUG}/"
cp -r includes "dist/${PLUGIN_SLUG}/"
cp rationalcleanup.php "dist/${PLUGIN_SLUG}/"
cp readme.txt "dist/${PLUGIN_SLUG}/"

# Copy LICENSE if it exists
if [ -f "LICENSE" ]; then
    cp LICENSE "dist/${PLUGIN_SLUG}/"
fi

# Copy screenshots if they exist
for screenshot in screenshot-*.png screenshot-*.jpg; do
    if [ -f "$screenshot" ]; then
        cp "$screenshot" "dist/${PLUGIN_SLUG}/"
    fi
done

# Create zip archive
echo "Creating zip archive..."
cd dist
zip -r "${PLUGIN_SLUG}-${VERSION}.zip" "${PLUGIN_SLUG}" -x "*.DS_Store"
cd ..

# Output result
echo ""
echo -e "${GREEN}Build complete!${NC}"
echo "Distribution package: dist/${PLUGIN_SLUG}-${VERSION}.zip"
echo ""

# Show package contents
echo "Package contents:"
unzip -l "dist/${PLUGIN_SLUG}-${VERSION}.zip"

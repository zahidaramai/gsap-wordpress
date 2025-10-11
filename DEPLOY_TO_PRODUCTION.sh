#!/bin/bash

##############################################################################
# GSAP WordPress - Deploy to Production Server
# Run this script to update your production server with latest fixes
##############################################################################

echo "========================================="
echo "GSAP WordPress - Production Deployment"
echo "========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Production server details (UPDATE THESE!)
PROD_SERVER="your-server.com"  # Change to your server address
PROD_USER="zhdstlr"             # Change to your SSH user
PROD_PATH="/home/zhdstlr/public_html/wp-content/plugins/gsap-wordpress-main"
GIT_REPO="https://github.com/zahidaramai/gsap-wordpress"

echo -e "${YELLOW}⚠️  IMPORTANT: Update server details in this script first!${NC}"
echo ""
echo "Server: $PROD_SERVER"
echo "User: $PROD_USER"
echo "Path: $PROD_PATH"
echo ""
read -p "Are these settings correct? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}❌ Deployment cancelled. Please update server details in the script.${NC}"
    exit 1
fi

echo ""
echo "📤 Deploying to production..."
echo ""

# Step 1: Ensure local changes are committed
echo "1️⃣  Checking local repository..."
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}⚠️  You have uncommitted changes. Commit them first!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Local repository is clean${NC}"

# Step 2: Push to GitHub
echo ""
echo "2️⃣  Pushing to GitHub..."
git push origin main
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Pushed to GitHub successfully${NC}"
else
    echo -e "${RED}❌ Failed to push to GitHub${NC}"
    exit 1
fi

# Step 3: Deploy to production server via SSH
echo ""
echo "3️⃣  Connecting to production server..."
echo ""

ssh -t "${PROD_USER}@${PROD_SERVER}" << SSHEOF
    echo "🔄 Updating plugin on production server..."
    cd "${PROD_PATH}" || exit 1
    
    # Pull latest changes
    echo "Pulling latest changes from GitHub..."
    git pull origin main
    
    if [ \$? -eq 0 ]; then
        echo "✓ Code updated successfully"
    else
        echo "❌ Failed to pull from GitHub"
        exit 1
    fi
    
    # Clear OPCache if available
    echo "Clearing OPCache..."
    php -r "if(function_exists('opcache_reset')) { opcache_reset(); echo '✓ OPCache cleared'; } else { echo 'ℹ️  OPCache not available'; }"
    
    # Clear any plugin caches
    if [ -d "../../cache" ]; then
        echo "Clearing cache directory..."
        rm -rf ../../cache/*
        echo "✓ Cache cleared"
    fi
    
    echo ""
    echo "🎉 Deployment complete!"
    echo ""
    echo "Next steps:"
    echo "1. Go to WordPress Admin → Plugins"
    echo "2. Deactivate 'GSAP for WordPress'"
    echo "3. Reactivate 'GSAP for WordPress'"  
    echo "4. You should see ZERO warnings!"
    echo ""
SSHEOF

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}=========================================${NC}"
    echo -e "${GREEN}✅ DEPLOYMENT SUCCESSFUL!${NC}"
    echo -e "${GREEN}=========================================${NC}"
    echo ""
    echo "📋 Post-Deployment Checklist:"
    echo "  1. Visit your WordPress admin"
    echo "  2. Deactivate & Reactivate the plugin"
    echo "  3. Check for warnings (should be ZERO!)"
    echo "  4. Test save settings functionality"
    echo ""
else
    echo ""
    echo -e "${RED}❌ Deployment failed!${NC}"
    echo "Check the error messages above."
    exit 1
fi

#!/bin/bash

# Project path
PROJECT_DIR="/home/u223469901/domains/aavirbhav.tech/public_html/assets/razorpay"

echo "🚀 Starting deployment at $(date)"

# Go to project folder
cd $PROJECT_DIR || exit

# Pull latest code
echo "📥 Pulling latest code..."
git reset --hard
git pull origin main

# Remove old vendor and lock file (to avoid conflicts)
echo "🧹 Cleaning old dependencies..."
rm -rf vendor composer.lock

# Install dependencies with composer (will trigger post-install scripts)
echo "📦 Installing composer dependencies..."
/usr/bin/composer install --no-dev --optimize-autoloader

# Just in case, run dump-autoload manually too
/usr/bin/composer dump-autoload -o

# Permissions (optional, adjust for your hosting setup)
echo "🔑 Setting permissions..."
chmod -R 755 $PROJECT_DIR
chown -R $USER:$USER $PROJECT_DIR

echo "✅ Deployment completed at $(date)"
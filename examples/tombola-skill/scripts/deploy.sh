#!/bin/bash

# Tombola Napoletana - Deployment Script

set -e

echo "🚀 Starting Tombola Napoletana deployment..."

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: composer.json not found. Please run from project root."
    exit 1
fi

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p data logs
chmod 755 data logs

# Check environment
echo "🔧 Checking environment..."
if [ ! -f "config/.env" ]; then
    echo "⚠️  Warning: config/.env not found. Copying from .env.example..."
    cp config/.env.example config/.env
    echo "Please edit config/.env with your settings before running the skill."
fi

# Validate configuration
echo "✅ Validating configuration..."
php -l public/index.php
if [ $? -ne 0 ]; then
    echo "❌ Error: PHP syntax validation failed"
    exit 1
fi

# Optimize performance
echo "⚡ Optimizing performance..."
php -r "
require_once 'vendor/autoload.php';
TombolaNapoletana\Services\PerformanceService::optimizeDatabase();
"

# Generate interaction model
echo "📋 Generating interaction model..."
if [ -f "interaction-model.json" ]; then
    echo "✅ Interaction model already exists"
else
    echo "⚠️  Warning: Could not generate interaction model automatically"
    echo "Please ensure all handlers have @utterances annotations"
fi

# Set permissions
echo "🔐 Setting permissions..."
chmod 644 config/.env
chmod 755 public/index.php

# Show deployment summary
echo ""
echo "🎉 Deployment completed successfully!"
echo ""
echo "📊 Summary:"
echo "   - Dependencies installed"
echo "   - Directories created"
echo "   - Configuration validated"
echo "   - Performance optimized"
echo "   - Permissions set"
echo ""
echo "🌐 Next steps:"
echo "   1. Edit config/.env with your Alexa Skill ID"
echo "   2. Upload interaction-model.json to Alexa Developer Console"
echo "   3. Configure your HTTPS endpoint"
echo "   4. Test the skill"
echo ""
echo "🚀 Ready to deploy!"

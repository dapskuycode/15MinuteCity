#!/bin/bash

# Script untuk menjalankan Laravel server dengan network access
echo "🚀 Starting Laravel server with network access..."
echo "📡 Server will be available at: http://0.0.0.0:8000"
echo "🌐 Network IPs:"
echo "   - http://192.168.1.5:8000"
echo "   - http://192.168.100.2:8000"
echo "   - http://192.168.139.1:8000"
echo "   - http://192.168.232.1:8000"
echo ""

# Jalankan Laravel server dengan host 0.0.0.0 untuk network access
php artisan serve --host=0.0.0.0 --port=8000 
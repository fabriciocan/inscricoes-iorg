#!/bin/bash

echo "Parando servidor PHP..."
pkill -f "php artisan serve"
sleep 2

echo "Limpando todos os caches..."
rm -f bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*

echo "Recriando cache de configuração..."
php artisan config:cache

echo "Iniciando servidor..."
php artisan serve --host=127.0.0.1 --port=8000

echo "Servidor iniciado!"

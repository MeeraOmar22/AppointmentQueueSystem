#!/bin/bash
cd "$(dirname "$0")"
php artisan test 2>&1

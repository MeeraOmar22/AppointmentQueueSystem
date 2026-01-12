#!/usr/bin/env php
<?php

/**
 * Dental Clinic Test Runner Script
 * 
 * Usage:
 *   php run_tests.php                 # Run all tests
 *   php run_tests.php unit            # Run unit tests
 *   php run_tests.php feature         # Run feature tests
 *   php run_tests.php coverage        # Run with coverage report
 */

$command = $_SERVER['argv'][1] ?? 'all';

$commands = [
    'all'       => 'php artisan test',
    'unit'      => 'php artisan test --testsuite=Unit',
    'feature'   => 'php artisan test --testsuite=Feature',
    'coverage'  => 'php artisan test --coverage --min=75',
    'parallel'  => 'php artisan test --parallel',
    'verbose'   => 'php artisan test --verbose',
    'bail'      => 'php artisan test --bail',
    'models'    => 'php artisan test tests/Unit/ --filter=Model',
    'services'  => 'php artisan test tests/Unit/Services/',
    'api'       => 'php artisan test tests/Feature/Api/',
    'integration' => 'php artisan test tests/Feature/Integration/',
];

if (!isset($commands[$command])) {
    echo "Available test commands:\n";
    foreach (array_keys($commands) as $cmd) {
        echo "  - $cmd\n";
    }
    exit(1);
}

echo "Running: {$commands[$command]}\n";
echo str_repeat('=', 60) . "\n";

passthru($commands[$command]);

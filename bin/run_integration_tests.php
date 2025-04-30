<?php

// Setup test database
require_once __DIR__ . '/setup_test_db.php';

// Run the integration tests
$command = 'vendor/bin/phpunit --testsuite Integration';
echo "Running integration tests...\n";
passthru($command, $exitCode);
exit($exitCode); 
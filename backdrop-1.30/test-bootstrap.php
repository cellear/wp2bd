<?php
/**
 * Minimal bootstrap test to isolate the error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BACKDROP_ROOT', __DIR__);

echo "Step 1: Defining BACKDROP_ROOT\n";

// Try to load bootstrap
echo "Step 2: Loading bootstrap.inc\n";
require_once BACKDROP_ROOT . '/core/includes/bootstrap.inc';

echo "Step 3: bootstrap.inc loaded successfully\n";

// Try minimal bootstrap
echo "Step 4: Calling backdrop_bootstrap(BACKDROP_BOOTSTRAP_CONFIGURATION)\n";
try {
    backdrop_bootstrap(BACKDROP_BOOTSTRAP_CONFIGURATION);
    echo "Step 5: Configuration bootstrap successful\n";
} catch (Exception $e) {
    echo "ERROR in configuration bootstrap: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "Test complete\n";

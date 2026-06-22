<?php
/**
 * Unit test bootstrap — defines stubs so PSR-4-loaded files
 * with `defined('ABSPATH') || exit` don't kill the process.
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wp/');
}

require_once __DIR__ . '/../vendor/autoload.php';

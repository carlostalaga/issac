<?php
/**
 * Plugin Name: ISSAC Assessment
 * Description: Inclusive Schools Self-Assessment Checklist platform.
 * Version:     0.1.0
 * Requires PHP: 8.1
 * Author:      Milacku
 */
defined('ABSPATH') || exit;
require_once __DIR__ . '/vendor/autoload.php';
register_activation_hook(__FILE__, ['Issac\Install\Activator', 'activate']);
<?php
/**
 * Plugin Name: ISSAC Assessment
 * Description: Inclusive Schools Self-Assessment Checklist platform.
 * Version:     0.1.0
 * Requires PHP: 8.1
 * Author:      Milacku
 */
defined('ABSPATH') || exit;

define('ISSAC_FILE', __FILE__);
define('ISSAC_PATH', plugin_dir_path(__FILE__));
define('ISSAC_URL', plugin_dir_url(__FILE__));
define('ISSAC_VERSION', '0.1.0');

require_once __DIR__ . '/vendor/autoload.php';

register_activation_hook(__FILE__, ['Issac\Install\Activator', 'activate']);

Issac\Plugin::boot();

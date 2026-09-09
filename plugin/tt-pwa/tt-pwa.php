<?php
/**
 * Plugin Name: TT PWA
 * Description: Minimal same-origin PWA manifest and conservative service worker for the storefront.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Thực phẩm Thủy Trang
 * License: GPL-2.0-or-later
 * Text Domain: tt-pwa
 */

defined( 'ABSPATH' ) || exit;
define( 'TT_PWA_FILE', __FILE__ );
define( 'TT_PWA_VERSION', '0.1.0' );
require_once __DIR__ . '/src/Pwa.php';
TT_PWA::init();

<?php
/**
 * Plugin Name: TT Product QR
 * Description: Local canonical product QR codes and protected thermal-label printing.
 * Version: 0.2.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Thực phẩm Thủy Trang
 * License: GPL-2.0-or-later
 * Text Domain: tt-product-qr
 */

defined( 'ABSPATH' ) || exit;
define( 'TT_PRODUCT_QR_FILE', __FILE__ );
define( 'TT_PRODUCT_QR_VERSION', '0.2.0' );
require_once __DIR__ . '/src/ProductQr.php';
TT_Product_QR::init();

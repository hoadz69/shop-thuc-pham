<?php
defined( 'ABSPATH' ) || exit;

function tt_setup_theme() {
	load_child_theme_textdomain( 'thuc-pham-thuy-trang', get_stylesheet_directory() . '/languages' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'tt_setup_theme' );

function tt_enqueue_assets() {
	$style_path = get_stylesheet_directory() . '/assets/css/site.css';
	$script_path = get_stylesheet_directory() . '/assets/js/site.js';
	wp_enqueue_style( 'tt-site', get_stylesheet_directory_uri() . '/assets/css/site.css', array(), (string) filemtime( $style_path ) );
	wp_enqueue_script( 'tt-site', get_stylesheet_directory_uri() . '/assets/js/site.js', array(), (string) filemtime( $script_path ), true );
}
add_action( 'wp_enqueue_scripts', 'tt_enqueue_assets', 20 );

function tt_body_classes( $classes ) {
	$classes[] = 'tt-storefront';
	return $classes;
}
add_filter( 'body_class', 'tt_body_classes' );

function tt_mobile_navigation() {
	if ( is_admin() ) { return; }
	?>
	<nav class="tt-mobile-nav" aria-label="<?php esc_attr_e( 'Điều hướng nhanh', 'thuc-pham-thuy-trang' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><span aria-hidden="true">⌂</span><?php esc_html_e( 'Trang chủ', 'thuc-pham-thuy-trang' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/products/' ) ); ?>"><span aria-hidden="true">▦</span><?php esc_html_e( 'Sản phẩm', 'thuc-pham-thuy-trang' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><span aria-hidden="true">⌕</span><?php esc_html_e( 'Tìm kiếm', 'thuc-pham-thuy-trang' ); ?></a>
		<a href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ) ); ?>"><span aria-hidden="true">◉</span><?php esc_html_e( 'Giỏ hàng', 'thuc-pham-thuy-trang' ); ?></a>
	</nav>
	<?php
}
add_action( 'wp_footer', 'tt_mobile_navigation', 30 );

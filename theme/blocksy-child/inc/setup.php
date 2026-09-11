<?php
defined( 'ABSPATH' ) || exit;

function tt_setup_theme() {
	load_child_theme_textdomain( 'thuc-pham-thuy-trang', get_stylesheet_directory() . '/languages' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'tt_setup_theme' );

function tt_site_icon_url( $url, $size, $blog_id ) {
	if ( $url ) {
		return $url;
	}

	return get_stylesheet_directory_uri() . '/assets/images/site-icon-512.png';
}
add_filter( 'get_site_icon_url', 'tt_site_icon_url', 10, 3 );

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

function tt_topbar() {
	$phone = function_exists( 'tt_store_setting' ) ? tt_store_setting( 'phone' ) : '';
	$email = function_exists( 'tt_store_setting' ) ? tt_store_setting( 'email' ) : '';
	if ( ! $phone && ! $email ) { return; }
	?>
	<div class="tt-topbar">
		<div class="tt-shell tt-topbar__inner">
			<span><?php esc_html_e( 'Thực phẩm tươi sạch, giao hàng tận tâm', 'thuc-pham-thuy-trang' ); ?></span>
			<div class="tt-topbar__contact">
				<?php if ( $phone ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php esc_html_e( 'Điện thoại:', 'thuc-pham-thuy-trang' ); ?> <?php echo esc_html( $phone ); ?></a><?php endif; ?>
				<?php if ( $email ) : ?><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a><?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'blocksy:header:before', 'tt_topbar' );

function tt_site_header() {
	$logo_url = get_stylesheet_directory_uri() . '/assets/images/logo-thuy-trang.svg';
	$items = array(
		home_url( '/' )            => 'Trang chủ',
		home_url( '/products/' )   => 'Sản phẩm',
		home_url( '/bai-viet/' )   => 'Bài viết',
		home_url( '/doi-tac/' )    => 'Đối tác',
		home_url( '/gioi-thieu/' ) => 'Giới thiệu',
		home_url( '/lien-he/' )    => 'Liên hệ',
	);
	?>
	<header id="tt-site-header" class="tt-site-header" itemscope itemtype="https://schema.org/WPHeader">
		<div class="tt-shell tt-site-header__inner">
			<a class="tt-site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="360" height="88">
			</a>
			<nav id="tt-main-nav" class="tt-main-nav" aria-label="<?php esc_attr_e( 'Menu chính', 'thuc-pham-thuy-trang' ); ?>">
				<?php foreach ( $items as $url => $label ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a><?php endforeach; ?>
			</nav>
			<div class="tt-header-actions">
				<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Tìm kiếm', 'thuc-pham-thuy-trang' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6"></circle><path d="m16 16 4 4"></path></svg></a>
				<a href="<?php echo esc_url( home_url( '/tai-khoan/' ) ); ?>" aria-label="<?php esc_attr_e( 'Tài khoản', 'thuc-pham-thuy-trang' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 21c.8-4.1 3.3-6.2 7.5-6.2s6.7 2.1 7.5 6.2"></path></svg></a>
				<button class="tt-menu-toggle" type="button" aria-expanded="false" aria-controls="tt-main-nav"><span></span><span></span><span></span><span class="screen-reader-text"><?php esc_html_e( 'Mở menu', 'thuc-pham-thuy-trang' ); ?></span></button>
			</div>
		</div>
	</header>
	<?php
}
add_action( 'blocksy:header:before', 'tt_site_header', 20 );

function tt_mobile_navigation() {
	if ( is_admin() ) { return; }
	?>
	<nav class="tt-mobile-nav" aria-label="<?php esc_attr_e( 'Điều hướng nhanh', 'thuc-pham-thuy-trang' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><span aria-hidden="true">⌂</span><?php esc_html_e( 'Trang chủ', 'thuc-pham-thuy-trang' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/products/' ) ); ?>"><span aria-hidden="true">▦</span><?php esc_html_e( 'Sản phẩm', 'thuc-pham-thuy-trang' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><span aria-hidden="true">⌕</span><?php esc_html_e( 'Tìm kiếm', 'thuc-pham-thuy-trang' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>"><span aria-hidden="true">☎</span><?php esc_html_e( 'Liên hệ', 'thuc-pham-thuy-trang' ); ?></a>
	</nav>
	<?php
}
add_action( 'wp_footer', 'tt_mobile_navigation', 30 );

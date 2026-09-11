<?php
$root = dirname( __DIR__, 2 );
$style = file_get_contents( $root . '/theme/blocksy-child/style.css' );
$setup = file_get_contents( $root . '/theme/blocksy-child/inc/setup.php' );
$logo = file_get_contents( $root . '/theme/blocksy-child/assets/images/logo-thuy-trang.svg' );
$site_icon_svg = file_get_contents( $root . '/theme/blocksy-child/assets/images/site-icon-logo.svg' );
$site_icon = $root . '/theme/blocksy-child/assets/images/site-icon-logo-transparent-512.png';
foreach ( array( 'Template: blocksy', 'Text Domain: thuc-pham-thuy-trang' ) as $needle ) {
	if ( false === strpos( $style, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( 'wp_enqueue_style', 'filemtime', 'add_theme_support' ) as $needle ) {
	if ( false === strpos( $setup, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( 'tt-site-header', 'Trang chủ', 'Sản phẩm', 'Bài viết', 'Đối tác', 'Giới thiệu', 'Liên hệ', 'tt-menu-toggle' ) as $needle ) {
	if ( false === strpos( $setup, $needle ) ) { fwrite( STDERR, "Missing header element $needle\n" ); exit( 1 ); }
}
if ( false === strpos( $logo, '<svg' ) || false === strpos( $logo, 'THỦY TRANG' ) ) { fwrite( STDERR, "Missing original SVG logo\n" ); exit( 1 ); }
if ( false === strpos( $setup, 'get_site_icon_url' ) || false === strpos( $setup, 'site-icon-logo-transparent-512.png' ) ) { fwrite( STDERR, "Missing storefront site icon filter\n" ); exit( 1 ); }
foreach ( array( 'M39 67C18 57', 'M39 64c1-17', '#ee5d2f' ) as $logo_mark ) {
	if ( false === strpos( $logo, $logo_mark ) || false === strpos( $site_icon_svg, $logo_mark ) ) { fwrite( STDERR, "Site icon does not match the header logo mark\n" ); exit( 1 ); }
}
if ( ! is_file( $site_icon ) || getimagesize( $site_icon )[0] !== 512 || getimagesize( $site_icon )[1] !== 512 ) { fwrite( STDERR, "Invalid storefront site icon\n" ); exit( 1 ); }
echo "PASS\n";

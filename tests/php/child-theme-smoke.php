<?php
$root = dirname( __DIR__, 2 );
$style = file_get_contents( $root . '/theme/blocksy-child/style.css' );
$setup = file_get_contents( $root . '/theme/blocksy-child/inc/setup.php' );
$logo = file_get_contents( $root . '/theme/blocksy-child/assets/images/logo-thuy-trang.svg' );
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
echo "PASS\n";

<?php
$root = dirname( __DIR__, 2 );
$home = file_get_contents( $root . '/theme/blocksy-child/inc/home.php' );
$woo = file_get_contents( $root . '/theme/blocksy-child/inc/woocommerce.php' );
$settings = file_get_contents( $root . '/theme/blocksy-child/inc/store-settings.php' );
$footer = file_get_contents( $root . '/theme/blocksy-child/inc/footer.php' );
$css = file_get_contents( $root . '/theme/blocksy-child/assets/css/site.css' );
foreach ( array( '<h1>', 'tt-hero', 'tt-promises', 'Thực phẩm tươi sạch mỗi tuần', 'posts_per_page', 'wp_reset_postdata' ) as $needle ) {
	if ( false === strpos( $home, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( 'WC_Widget_Product_Categories', 'WC_Widget_Price_Filter' ) as $needle ) {
	if ( false === strpos( $woo, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( 'customize_register', 'Thông tin cửa hàng', 'blogname', 'tt_phone', 'tt_email', 'tt_address' ) as $needle ) {
	if ( false === strpos( $settings, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( 'tt-site-footer', 'Địa chỉ liên hệ', 'Danh mục sản phẩm', 'Bản tin', 'wp_nonce_field', 'product_cat', 'blocksy:footer:before' ) as $needle ) {
	if ( false === strpos( $footer, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( '@media(max-width:1024px)', '@media(max-width:768px)', '@media(max-width:480px)', 'focus-visible', 'aspect-ratio' ) as $needle ) {
	if ( false === strpos( $css, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
echo "PASS\n";

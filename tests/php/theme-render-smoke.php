<?php
$root = dirname( __DIR__, 2 );
$home = file_get_contents( $root . '/theme/blocksy-child/inc/home.php' );
$woo = file_get_contents( $root . '/theme/blocksy-child/inc/woocommerce.php' );
$settings = file_get_contents( $root . '/theme/blocksy-child/inc/store-settings.php' );
$footer = file_get_contents( $root . '/theme/blocksy-child/inc/footer.php' );
$css = file_get_contents( $root . '/theme/blocksy-child/assets/css/site.css' );
foreach ( array( '<h1>', 'tt-hero', 'tt-promises', 'tt-weekly-grid', 'Thực phẩm tươi sạch mỗi tuần', 'tt-category-products', 'Sản phẩm theo danh mục', 'tt-tips', 'posts_per_page', 'wp_reset_postdata' ) as $needle ) {
	if ( false === strpos( $home, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
if ( false === strpos( $home, "'posts_per_page' => 4" ) ) { fwrite( STDERR, "Featured products must be limited to four cards\n" ); exit( 1 ); }
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
foreach ( array( '.tt-topbar', '.tt-featured .woocommerce-loop-product__title a', '.tt-featured .ct-woo-card-actions', '.tt-featured .meta-categories', '.tt-weekly-card', '.tt-mini-product', '.tt-tip-card' ) as $needle ) {
	if ( false === strpos( $css, $needle ) ) { fwrite( STDERR, "Missing featured-card style $needle\n" ); exit( 1 ); }
}
echo "PASS\n";

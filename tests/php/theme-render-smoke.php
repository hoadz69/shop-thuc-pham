<?php
$root = getenv( 'TT_PROJECT_ROOT' ) ?: dirname( __DIR__, 2 );
$theme_root = getenv( 'TT_THEME_ROOT' ) ?: $root . '/theme/blocksy-child';
$home = file_get_contents( $theme_root . '/inc/home.php' );
$woo = file_get_contents( $theme_root . '/inc/woocommerce.php' );
$settings = file_get_contents( $theme_root . '/inc/store-settings.php' );
$footer = file_get_contents( $theme_root . '/inc/footer.php' );
$css = file_get_contents( $theme_root . '/assets/css/site.css' );
$js = file_get_contents( $theme_root . '/assets/js/site.js' );
foreach ( array( '<h1>', 'tt-hero', 'tt-promises', 'tt-weekly-grid', 'Thực phẩm tươi sạch mỗi tuần', 'tt-category-products', 'Sản phẩm theo danh mục', 'tt-tips', 'posts_per_page', 'wp_reset_postdata' ) as $needle ) {
	if ( false === strpos( $home, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( "function_exists( 'wc_get_template_part' )", "function_exists( 'wc_get_product' )", '$woocommerce_ready' ) as $needle ) {
	if ( false === strpos( $home, $needle ) ) { fwrite( STDERR, "Missing WooCommerce availability guard $needle\n" ); exit( 1 ); }
}
if ( false === strpos( $home, "'posts_per_page' => 4" ) ) { fwrite( STDERR, "Featured products must be limited to four cards\n" ); exit( 1 ); }
foreach ( array( 'WC_Widget_Product_Categories', 'WC_Widget_Price_Filter' ) as $needle ) {
	if ( false === strpos( $woo, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( 'woocommerce_loop_add_to_cart_link', 'Xem chi tiết', 'tt-product-contact', 'Liên hệ nhanh với Thủy Trang', 'tt-contact-action--phone', 'tt-contact-action--email', 'woocommerce_is_purchasable', 'woocommerce_variation_is_purchasable', 'woocommerce_add_to_cart_validation', 'woocommerce_template_single_add_to_cart', 'TT_Product_QR::render_frontend' ) as $needle ) {
	if ( false === strpos( $woo, $needle ) ) { fwrite( STDERR, "Missing catalog behavior $needle\n" ); exit( 1 ); }
}
foreach ( array( 'customize_register', 'Thông tin cửa hàng', 'blogname', "'tt_' . ", "'phone'", "'email'", "'address'" ) as $needle ) {
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
foreach ( array( 'tt-hero-slider', 'tt-hero__slide', 'tt-hero__media', 'loading="eager"', 'aria-roledescription="carousel"', 'tt-hero__dots', 'data-tt-slide' ) as $needle ) {
	if ( false === strpos( $home, $needle ) ) { fwrite( STDERR, "Missing homepage slider markup $needle\n" ); exit( 1 ); }
}
foreach ( array( '.tt-hero-slider', '.tt-hero__slide', '.tt-hero__arrow', '.tt-hero__dot', 'prefers-reduced-motion' ) as $needle ) {
	if ( false === strpos( $css, $needle ) ) { fwrite( STDERR, "Missing homepage slider style $needle\n" ); exit( 1 ); }
}
foreach ( array( '[data-tt-slider]', 'setInterval', '10000', 'aria-current', 'visibilitychange', 'prefers-reduced-motion' ) as $needle ) {
	if ( false === strpos( $js, $needle ) ) { fwrite( STDERR, "Missing homepage slider behavior $needle\n" ); exit( 1 ); }
}
foreach ( array( 'hero-vegetables-centered-v3.png', 'hero-family-meal-centered-v3.png' ) as $asset ) {
	if ( ! is_file( $theme_root . '/assets/images/' . $asset ) ) { fwrite( STDERR, "Missing homepage slider asset $asset\n" ); exit( 1 ); }
}
echo "PASS\n";

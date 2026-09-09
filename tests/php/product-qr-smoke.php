<?php
$root = dirname( __DIR__, 2 );
$source = file_get_contents( $root . '/plugin/tt-product-qr/src/ProductQr.php' );
$js = file_get_contents( $root . '/plugin/tt-product-qr/assets/product-qr.js' );
foreach ( array( 'get_permalink( $product_id )', "array( 'http', 'https' )", 'wp_http_validate_url', 'aria-label', 'data-qr-payload', "current_user_can( 'edit_products' )", 'check_admin_referer', 'absint', 'esc_html', 'esc_url' ) as $needle ) {
	if ( false === strpos( $source, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
if ( false === strpos( $js, '/^https?:\\/\\//i' ) ) { fwrite( STDERR, "Missing URL scheme guard\n" ); exit( 1 ); }
foreach ( array( 'customer', 'password', 'token' ) as $forbidden ) {
	if ( false !== stripos( $source, $forbidden ) ) { fwrite( STDERR, "Forbidden output concept: $forbidden\n" ); exit( 1 ); }
}
echo "PASS\n";

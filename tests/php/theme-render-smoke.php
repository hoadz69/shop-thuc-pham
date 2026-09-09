<?php
$root = dirname( __DIR__, 2 );
$home = file_get_contents( $root . '/theme/blocksy-child/inc/home.php' );
$woo = file_get_contents( $root . '/theme/blocksy-child/inc/woocommerce.php' );
$css = file_get_contents( $root . '/theme/blocksy-child/assets/css/site.css' );
foreach ( array( '<h1>', 'tt-hero', 'tt-promises', 'Thực phẩm tươi sạch mỗi tuần', 'posts_per_page', 'wp_reset_postdata' ) as $needle ) {
	if ( false === strpos( $home, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( 'WC_Widget_Product_Categories', 'WC_Widget_Price_Filter' ) as $needle ) {
	if ( false === strpos( $woo, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( '@media(max-width:1024px)', '@media(max-width:768px)', '@media(max-width:480px)', 'focus-visible', 'aspect-ratio' ) as $needle ) {
	if ( false === strpos( $css, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
echo "PASS\n";

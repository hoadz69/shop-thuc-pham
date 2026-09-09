<?php
$root = dirname( __DIR__, 2 );
$style = file_get_contents( $root . '/theme/blocksy-child/style.css' );
$setup = file_get_contents( $root . '/theme/blocksy-child/inc/setup.php' );
foreach ( array( 'Template: blocksy', 'Text Domain: thuc-pham-thuy-trang' ) as $needle ) {
	if ( false === strpos( $style, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
foreach ( array( 'wp_enqueue_style', 'filemtime', 'add_theme_support' ) as $needle ) {
	if ( false === strpos( $setup, $needle ) ) { fwrite( STDERR, "Missing $needle\n" ); exit( 1 ); }
}
echo "PASS\n";

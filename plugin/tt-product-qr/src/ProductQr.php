<?php
defined( 'ABSPATH' ) || exit;

final class TT_Product_QR {
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_frontend' ), 36 );
		add_action( 'add_meta_boxes_product', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'admin_post_tt_print_product_qr', array( __CLASS__, 'print_label' ) );
	}

	public static function enqueue_assets() {
		if ( ! is_admin() && ! is_product() ) { return; }
		$base = plugin_dir_url( TT_PRODUCT_QR_FILE );
		wp_enqueue_style( 'tt-product-qr', $base . 'assets/product-qr.css', array(), TT_PRODUCT_QR_VERSION );
		wp_enqueue_script( 'tt-qrcode', $base . 'vendor/qrcodejs/qrcode.min.js', array(), '04f46c6', true );
		wp_enqueue_script( 'tt-product-qr', $base . 'assets/product-qr.js', array( 'tt-qrcode' ), TT_PRODUCT_QR_VERSION, true );
	}

	public static function product_url( $product_id ) {
		$url = get_permalink( $product_id );
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		return in_array( $scheme, array( 'http', 'https' ), true ) && wp_http_validate_url( $url ) ? $url : '';
	}

	private static function qr_markup( $product_id, $size = 180 ) {
		$url = self::product_url( $product_id );
		if ( ! $url ) { return ''; }
		$label = sprintf( __( 'Mã QR mở trang sản phẩm %s', 'tt-product-qr' ), get_the_title( $product_id ) );
		return sprintf( '<div class="tt-product-qr-code" role="img" aria-label="%1$s" data-qr-payload="%2$s" data-qr-size="%3$d"></div>', esc_attr( $label ), esc_url( $url ), absint( $size ) );
	}

	private static function safe_qr_markup( $product_id, $size = 180 ) {
		return wp_kses(
			self::qr_markup( $product_id, $size ),
			array(
				'div' => array(
					'class'           => true,
					'role'            => true,
					'aria-label'      => true,
					'data-qr-payload' => true,
					'data-qr-size'    => true,
				),
			)
		);
	}

	public static function render_frontend() {
		global $product;
		if ( ! $product instanceof WC_Product ) { return; }
		echo '<section class="tt-product-qr-panel"><h2>' . esc_html__( 'Quét để mở sản phẩm', 'tt-product-qr' ) . '</h2>';
		echo self::safe_qr_markup( $product->get_id() );
		echo '<p>' . esc_html__( 'QR thử nghiệm theo URL hiện tại; tem chính thức được tạo sau khi có domain HTTPS.', 'tt-product-qr' ) . '</p></section>';
	}

	public static function add_meta_box() {
		add_meta_box( 'tt-product-qr', __( 'QR và tem sản phẩm', 'tt-product-qr' ), array( __CLASS__, 'meta_box' ), 'product', 'side' );
	}

	public static function meta_box( $post ) {
		echo self::safe_qr_markup( $post->ID, 150 );
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=tt_print_product_qr&product_id=' . absint( $post->ID ) ), 'tt_print_product_qr_' . absint( $post->ID ) );
		printf( '<p><a class="button button-primary" href="%s" target="_blank" rel="noopener">%s</a></p>', esc_url( $url ), esc_html__( 'In tem thử', 'tt-product-qr' ) );
	}

	public static function print_label() {
		if ( ! current_user_can( 'edit_products' ) ) { wp_die( esc_html__( 'Bạn không có quyền in tem.', 'tt-product-qr' ), '', array( 'response' => 403 ) ); }
		$product_id = isset( $_GET['product_id'] ) ? absint( wp_unslash( $_GET['product_id'] ) ) : 0;
		check_admin_referer( 'tt_print_product_qr_' . $product_id );
		$product = wc_get_product( $product_id );
		if ( ! $product ) { wp_die( esc_html__( 'Sản phẩm không tồn tại.', 'tt-product-qr' ), '', array( 'response' => 404 ) ); }
		$url = self::product_url( $product_id );
		if ( ! $url ) { wp_die( esc_html__( 'URL sản phẩm không hợp lệ.', 'tt-product-qr' ), '', array( 'response' => 400 ) ); }
		nocache_headers();
		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title><?php echo esc_html( $product->get_name() ); ?></title><link rel="stylesheet" href="<?php echo esc_url( plugin_dir_url( TT_PRODUCT_QR_FILE ) . 'assets/print.css?ver=' . TT_PRODUCT_QR_VERSION ); ?>"></head><body><main class="tt-label"><strong><?php echo esc_html( $product->get_name() ); ?></strong><span><?php echo wp_kses_post( $product->get_price_html() ); ?></span><?php echo self::safe_qr_markup( $product_id, 220 ); ?><small><?php echo esc_html( $product->get_sku() ); ?></small><small><?php echo esc_html( $url ); ?></small></main><script src="<?php echo esc_url( plugin_dir_url( TT_PRODUCT_QR_FILE ) . 'vendor/qrcodejs/qrcode.min.js' ); ?>"></script><script src="<?php echo esc_url( plugin_dir_url( TT_PRODUCT_QR_FILE ) . 'assets/product-qr.js?ver=' . TT_PRODUCT_QR_VERSION ); ?>"></script></body></html><?php
		exit;
	}
}

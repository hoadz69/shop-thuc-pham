<?php
defined( 'ABSPATH' ) || exit;

final class TT_Product_QR {
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_frontend' ), 36 );
		add_action( 'add_meta_boxes_product', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_post_tt_print_product_qr', array( __CLASS__, 'print_label' ) );
		add_action( 'admin_post_tt_print_product_qr_batch', array( __CLASS__, 'print_batch' ) );
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
		echo '<p>' . esc_html__( 'Quét mã để mở đúng trang chi tiết của sản phẩm.', 'tt-product-qr' ) . '</p>';
		if ( current_user_can( 'edit_products' ) ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=tt_print_product_qr&product_id=' . absint( $product->get_id() ) ), 'tt_print_product_qr_' . absint( $product->get_id() ) );
			printf( '<a class="tt-storefront-print-link" href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $url ), esc_html__( 'In tem QR', 'tt-product-qr' ) );
		}
		echo '</section>';
	}

	public static function add_meta_box() {
		add_meta_box( 'tt-product-qr', __( 'QR và tem sản phẩm', 'tt-product-qr' ), array( __CLASS__, 'meta_box' ), 'product', 'side' );
	}

	public static function meta_box( $post ) {
		echo self::safe_qr_markup( $post->ID, 150 );
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=tt_print_product_qr&product_id=' . absint( $post->ID ) ), 'tt_print_product_qr_' . absint( $post->ID ) );
		printf( '<p><a class="button button-primary" href="%s" target="_blank" rel="noopener">%s</a></p>', esc_url( $url ), esc_html__( 'In tem QR', 'tt-product-qr' ) );
	}

	public static function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'In tem QR sản phẩm', 'tt-product-qr' ),
			__( 'In tem QR', 'tt-product-qr' ),
			'edit_products',
			'tt-product-qr-labels',
			array( __CLASS__, 'labels_page' )
		);
	}

	public static function labels_page() {
		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( esc_html__( 'Bạn không có quyền in tem.', 'tt-product-qr' ), '', array( 'response' => 403 ) );
		}

		$products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => -1,
				'orderby' => 'name',
				'order'   => 'ASC',
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'In tem QR sản phẩm', 'tt-product-qr' ); ?></h1>
			<p><?php esc_html_e( 'Chọn mặt hàng và số tem cần in. Mỗi QR mở thẳng trang chi tiết sản phẩm trên website.', 'tt-product-qr' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" target="_blank">
				<input type="hidden" name="action" value="tt_print_product_qr_batch">
				<?php wp_nonce_field( 'tt_print_product_qr_batch' ); ?>
				<table class="widefat striped">
					<thead><tr>
						<td class="check-column"><input id="tt-select-all-products" type="checkbox" aria-label="<?php esc_attr_e( 'Chọn tất cả', 'tt-product-qr' ); ?>"></td>
						<th><?php esc_html_e( 'Sản phẩm', 'tt-product-qr' ); ?></th>
						<th><?php esc_html_e( 'SKU', 'tt-product-qr' ); ?></th>
						<th><?php esc_html_e( 'Giá', 'tt-product-qr' ); ?></th>
						<th><?php esc_html_e( 'Số tem', 'tt-product-qr' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $products as $product ) : ?>
						<tr>
							<th class="check-column"><input class="tt-product-checkbox" type="checkbox" name="product_ids[]" value="<?php echo absint( $product->get_id() ); ?>"></th>
							<td><a href="<?php echo esc_url( get_edit_post_link( $product->get_id() ) ); ?>"><strong><?php echo esc_html( $product->get_name() ); ?></strong></a></td>
							<td><?php echo esc_html( $product->get_sku() ?: '—' ); ?></td>
							<td><?php echo wp_kses_post( $product->get_price_html() ); ?></td>
							<td><input type="number" name="quantities[<?php echo absint( $product->get_id() ); ?>]" value="1" min="1" max="50" class="small-text"></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p><?php submit_button( __( 'Mở bản in tem đã chọn', 'tt-product-qr' ), 'primary', 'submit', false ); ?></p>
			</form>
		</div>
		<script>
		document.getElementById('tt-select-all-products').addEventListener('change', function (event) {
			document.querySelectorAll('.tt-product-checkbox').forEach(function (box) { box.checked = event.currentTarget.checked; });
		});
		</script>
		<?php
	}

	public static function print_label() {
		if ( ! current_user_can( 'edit_products' ) ) { wp_die( esc_html__( 'Bạn không có quyền in tem.', 'tt-product-qr' ), '', array( 'response' => 403 ) ); }
		$product_id = isset( $_GET['product_id'] ) ? absint( wp_unslash( $_GET['product_id'] ) ) : 0;
		check_admin_referer( 'tt_print_product_qr_' . $product_id );
		$product = wc_get_product( $product_id );
		if ( ! $product ) { wp_die( esc_html__( 'Sản phẩm không tồn tại.', 'tt-product-qr' ), '', array( 'response' => 404 ) ); }
		if ( ! self::product_url( $product_id ) ) { wp_die( esc_html__( 'URL sản phẩm không hợp lệ.', 'tt-product-qr' ), '', array( 'response' => 400 ) ); }
		self::print_document( array( $product ) );
	}

	public static function print_batch() {
		if ( ! current_user_can( 'edit_products' ) ) { wp_die( esc_html__( 'Bạn không có quyền in tem.', 'tt-product-qr' ), '', array( 'response' => 403 ) ); }
		check_admin_referer( 'tt_print_product_qr_batch' );

		$raw_ids = isset( $_POST['product_ids'] ) && is_array( $_POST['product_ids'] ) ? wp_unslash( $_POST['product_ids'] ) : array();
		$ids = array_values( array_unique( array_filter( array_map( 'absint', $raw_ids ) ) ) );
		if ( ! $ids ) { wp_die( esc_html__( 'Bạn chưa chọn sản phẩm.', 'tt-product-qr' ), '', array( 'response' => 400 ) ); }
		if ( count( $ids ) > 100 ) { wp_die( esc_html__( 'Mỗi lần chỉ được chọn tối đa 100 sản phẩm.', 'tt-product-qr' ), '', array( 'response' => 400 ) ); }

		$raw_quantities = isset( $_POST['quantities'] ) && is_array( $_POST['quantities'] ) ? wp_unslash( $_POST['quantities'] ) : array();
		$labels = array();
		foreach ( $ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product || 'publish' !== $product->get_status() || ! self::product_url( $product_id ) ) { continue; }
			$quantity = isset( $raw_quantities[ $product_id ] ) ? absint( $raw_quantities[ $product_id ] ) : 1;
			$quantity = max( 1, min( 50, $quantity ) );
			// Maximum 200 labels per request protects the browser and print queue.
			if ( count( $labels ) + $quantity > 200 ) { wp_die( esc_html__( 'Mỗi lần chỉ được in tối đa 200 tem.', 'tt-product-qr' ), '', array( 'response' => 400 ) ); }
			for ( $copy = 0; $copy < $quantity; $copy++ ) { $labels[] = $product; }
		}
		if ( ! $labels ) { wp_die( esc_html__( 'Không có sản phẩm hợp lệ để in.', 'tt-product-qr' ), '', array( 'response' => 400 ) ); }
		self::print_document( $labels );
	}

	private static function print_document( $products ) {
		nocache_headers();
		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title><?php esc_html_e( 'In tem QR sản phẩm', 'tt-product-qr' ); ?></title><link rel="stylesheet" href="<?php echo esc_url( plugin_dir_url( TT_PRODUCT_QR_FILE ) . 'assets/print.css?ver=' . TT_PRODUCT_QR_VERSION ); ?>"></head><body><div class="tt-print-actions"><button type="button" onclick="window.print()"><?php esc_html_e( 'In tem', 'tt-product-qr' ); ?></button></div><?php foreach ( $products as $product ) : $product_id = $product->get_id(); $url = self::product_url( $product_id ); ?><main class="tt-label"><strong><?php echo esc_html( $product->get_name() ); ?></strong><span><?php echo wp_kses_post( $product->get_price_html() ); ?></span><?php echo self::safe_qr_markup( $product_id, 220 ); ?><small><?php echo esc_html( $product->get_sku() ); ?></small><small><?php echo esc_html( $url ); ?></small></main><?php endforeach; ?><script src="<?php echo esc_url( plugin_dir_url( TT_PRODUCT_QR_FILE ) . 'vendor/qrcodejs/qrcode.min.js' ); ?>"></script><script src="<?php echo esc_url( plugin_dir_url( TT_PRODUCT_QR_FILE ) . 'assets/product-qr.js?ver=' . TT_PRODUCT_QR_VERSION ); ?>"></script></body></html><?php
		exit;
	}
}

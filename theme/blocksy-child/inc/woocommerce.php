<?php
defined( 'ABSPATH' ) || exit;

function tt_product_unit( $price, $product ) {
	$unit = $product->get_meta( '_tt_unit' );
	return $unit ? $price . '<small class="tt-unit">/' . esc_html( $unit ) . '</small>' : $price;
}
add_filter( 'woocommerce_get_price_html', 'tt_product_unit', 10, 2 );

function tt_loop_details_link( $html, $product ) {
	return sprintf(
		'<a class="button tt-details-button" href="%1$s" aria-label="%2$s">%3$s</a>',
		esc_url( get_permalink( $product->get_id() ) ),
		esc_attr( sprintf( __( 'Xem chi tiết sản phẩm %s', 'thuc-pham-thuy-trang' ), $product->get_name() ) ),
		esc_html__( 'Xem chi tiết', 'thuc-pham-thuy-trang' )
	);
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'tt_loop_details_link', 20, 2 );

function tt_catalog_mode_not_purchasable() {
	return false;
}
add_filter( 'woocommerce_is_purchasable', 'tt_catalog_mode_not_purchasable', 99 );
add_filter( 'woocommerce_variation_is_purchasable', 'tt_catalog_mode_not_purchasable', 99 );

function tt_catalog_mode_block_add_to_cart() {
	if ( function_exists( 'wc_add_notice' ) ) {
		wc_add_notice( __( 'Website hiện ở chế độ xem sản phẩm. Vui lòng liên hệ để đặt hàng.', 'thuc-pham-thuy-trang' ), 'notice' );
	}
	return false;
}
add_filter( 'woocommerce_add_to_cart_validation', 'tt_catalog_mode_block_add_to_cart', 99 );

function tt_catalog_mode_remove_purchase_controls() {
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	if ( class_exists( 'TT_Product_QR' ) ) {
		remove_action( 'woocommerce_single_product_summary', array( 'TT_Product_QR', 'render_frontend' ), 36 );
	}
}
add_action( 'wp', 'tt_catalog_mode_remove_purchase_controls' );

function tt_product_contact_panel() {
	if ( ! is_product() || ! function_exists( 'tt_store_setting' ) ) { return; }
	$phone = tt_store_setting( 'phone' );
	$email = tt_store_setting( 'email' );
	if ( ! $phone && ! $email ) { return; }
	?>
	<aside class="tt-product-contact" aria-labelledby="tt-product-contact-title">
		<div><p class="tt-eyebrow"><?php esc_html_e( 'Cần tư vấn thêm?', 'thuc-pham-thuy-trang' ); ?></p><h2 id="tt-product-contact-title"><?php esc_html_e( 'Liên hệ đặt hàng', 'thuc-pham-thuy-trang' ); ?></h2><p><?php esc_html_e( 'Trao đổi nhanh về số lượng, thời gian giao và sản phẩm phù hợp.', 'thuc-pham-thuy-trang' ); ?></p></div>
		<div class="tt-product-contact__actions">
			<?php if ( $phone ) : ?><a class="tt-button" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php esc_html_e( 'Gọi', 'thuc-pham-thuy-trang' ); ?> <?php echo esc_html( $phone ); ?></a><?php endif; ?>
			<?php if ( $email ) : ?><a class="tt-text-link" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php esc_html_e( 'Gửi email', 'thuc-pham-thuy-trang' ); ?></a><?php endif; ?>
		</div>
	</aside>
	<?php
}
function tt_catalog_mode_append_contact( $description ) {
	if ( ! is_product() ) { return $description; }
	ob_start();
	tt_product_contact_panel();
	if ( class_exists( 'TT_Product_QR' ) ) {
		TT_Product_QR::render_frontend();
	}
	return $description . ob_get_clean();
}
add_filter( 'woocommerce_short_description', 'tt_catalog_mode_append_contact', 20 );

function tt_shop_intro() {
	if ( is_shop() ) { echo '<div class="tt-shop-intro"><p class="tt-eyebrow">' . esc_html__( 'Chợ trực tuyến', 'thuc-pham-thuy-trang' ) . '</p><h1>' . esc_html__( 'Sản phẩm tươi ngon', 'thuc-pham-thuy-trang' ) . '</h1><p>' . esc_html__( 'Lọc theo danh mục hoặc mức giá để chọn nhanh thực phẩm phù hợp.', 'thuc-pham-thuy-trang' ) . '</p></div>'; }
}
add_action( 'woocommerce_before_main_content', 'tt_shop_intro', 5 );

function tt_shop_filters() {
	if ( ! is_shop() && ! is_product_taxonomy() ) { return; }
	echo '<aside class="tt-shop-filters" aria-label="' . esc_attr__( 'Bộ lọc sản phẩm', 'thuc-pham-thuy-trang' ) . '">';
	the_widget( 'WC_Widget_Product_Categories', array( 'title' => __( 'Danh mục sản phẩm', 'thuc-pham-thuy-trang' ), 'count' => 1 ) );
	the_widget( 'WC_Widget_Price_Filter', array( 'title' => __( 'Lọc theo giá', 'thuc-pham-thuy-trang' ) ) );
	echo '</aside>';
}
add_action( 'woocommerce_before_shop_loop', 'tt_shop_filters', 12 );

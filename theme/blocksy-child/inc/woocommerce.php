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
		<div class="tt-product-contact__heading">
			<span class="tt-product-contact__mark" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M7.5 4.5h-2A1.5 1.5 0 0 0 4 6c0 7.73 6.27 14 14 14a1.5 1.5 0 0 0 1.5-1.5v-2l-4-1-1 2a12.1 12.1 0 0 1-8-8l2-1-1-4Z"/></svg>
			</span>
			<div>
				<p class="tt-eyebrow"><?php esc_html_e( 'Tư vấn & đặt hàng', 'thuc-pham-thuy-trang' ); ?></p>
				<h2 id="tt-product-contact-title"><?php esc_html_e( 'Liên hệ nhanh với Thủy Trang', 'thuc-pham-thuy-trang' ); ?></h2>
			</div>
		</div>
		<p class="tt-product-contact__description"><?php esc_html_e( 'Chúng tôi hỗ trợ chọn sản phẩm, số lượng và thời gian giao phù hợp.', 'thuc-pham-thuy-trang' ); ?></p>
		<div class="tt-product-contact__actions">
			<?php if ( $phone ) : ?>
				<a class="tt-contact-action tt-contact-action--phone" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
					<span class="tt-contact-action__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7.5 4.5h-2A1.5 1.5 0 0 0 4 6c0 7.73 6.27 14 14 14a1.5 1.5 0 0 0 1.5-1.5v-2l-4-1-1 2a12.1 12.1 0 0 1-8-8l2-1-1-4Z"/></svg></span>
					<span><small><?php esc_html_e( 'Hotline đặt hàng', 'thuc-pham-thuy-trang' ); ?></small><strong><?php echo esc_html( $phone ); ?></strong></span>
				</a>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<a class="tt-contact-action tt-contact-action--email" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>">
					<span class="tt-contact-action__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 6.5h16v11H4z"/><path d="m4.5 7 7.5 6 7.5-6"/></svg></span>
					<span><small><?php esc_html_e( 'Tư vấn qua email', 'thuc-pham-thuy-trang' ); ?></small><strong><?php esc_html_e( 'Gửi email', 'thuc-pham-thuy-trang' ); ?></strong></span>
				</a>
			<?php endif; ?>
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

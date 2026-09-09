<?php
defined( 'ABSPATH' ) || exit;

function tt_product_unit( $price, $product ) {
	$unit = $product->get_meta( '_tt_unit' );
	return $unit ? $price . '<small class="tt-unit">/' . esc_html( $unit ) . '</small>' : $price;
}
add_filter( 'woocommerce_get_price_html', 'tt_product_unit', 10, 2 );

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

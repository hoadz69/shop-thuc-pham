<?php
defined( 'ABSPATH' ) || exit;

function tt_homepage_markup() {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/products/' );
	$hero_url = get_stylesheet_directory_uri() . '/assets/images/hero-fresh-market.png';
	ob_start();
	?>
	<div class="tt-home">
		<section class="tt-hero" style="--tt-hero-image:url('<?php echo esc_url( $hero_url ); ?>')">
			<div class="tt-shell tt-hero__content">
				<p class="tt-eyebrow"><?php esc_html_e( 'Tươi ngon mỗi ngày', 'thuc-pham-thuy-trang' ); ?></p>
				<h1><?php esc_html_e( 'Thực phẩm tươi sạch cho bữa cơm nhà', 'thuc-pham-thuy-trang' ); ?></h1>
				<p><?php esc_html_e( 'Lựa chọn thực phẩm thiết yếu thuận tiện, rõ giá và giao tận nơi.', 'thuc-pham-thuy-trang' ); ?></p>
				<div class="tt-actions"><a class="tt-button" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Mua sắm ngay', 'thuc-pham-thuy-trang' ); ?></a><a class="tt-text-link" href="<?php echo esc_url( home_url( '/gioi-thieu/' ) ); ?>"><?php esc_html_e( 'Câu chuyện của chúng tôi', 'thuc-pham-thuy-trang' ); ?></a></div>
			</div>
		</section>

		<section class="tt-promises tt-shell" aria-label="<?php esc_attr_e( 'Cam kết dịch vụ', 'thuc-pham-thuy-trang' ); ?>">
			<?php
			$promises = array(
				array( '✓', 'Chọn lọc kỹ', 'Nguồn hàng rõ ràng, ưu tiên độ tươi.' ),
				array( '↻', 'Đổi trả minh bạch', 'Hỗ trợ nhanh khi sản phẩm chưa đạt.' ),
				array( '♧', 'Đóng gói cẩn thận', 'Giữ sản phẩm sạch và gọn khi giao.' ),
				array( '☎', 'Tư vấn tận tâm', 'Sẵn sàng hỗ trợ cho từng đơn hàng.' ),
			);
			foreach ( $promises as $promise ) :
				?><article class="tt-promise"><span aria-hidden="true"><?php echo esc_html( $promise[0] ); ?></span><div><h2><?php echo esc_html( $promise[1] ); ?></h2><p><?php echo esc_html( $promise[2] ); ?></p></div></article><?php
			endforeach;
			?>
		</section>

		<section class="tt-categories tt-shell">
			<div class="tt-section-heading"><div><p class="tt-eyebrow"><?php esc_html_e( 'Danh mục nổi bật', 'thuc-pham-thuy-trang' ); ?></p><h2><?php esc_html_e( 'Đi chợ nhanh, chọn món dễ', 'thuc-pham-thuy-trang' ); ?></h2></div><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Xem tất cả', 'thuc-pham-thuy-trang' ); ?> →</a></div>
			<div class="tt-category-grid">
			<?php
			$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 7 ) );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					printf( '<a class="tt-category" href="%1$s"><span>%2$s</span><strong>%3$s</strong><small>%4$s</small></a>', esc_url( get_term_link( $term ) ), esc_html( mb_substr( $term->name, 0, 1 ) ), esc_html( $term->name ), esc_html( sprintf( _n( '%s sản phẩm', '%s sản phẩm', $term->count, 'thuc-pham-thuy-trang' ), number_format_i18n( $term->count ) ) ) );
				}
			}
			?>
			</div>
		</section>

		<section class="tt-weekly"><div class="tt-shell tt-weekly__inner"><div><p class="tt-eyebrow"><?php esc_html_e( 'Gợi ý cho gia đình', 'thuc-pham-thuy-trang' ); ?></p><h2><?php esc_html_e( 'Thực phẩm tươi sạch mỗi tuần', 'thuc-pham-thuy-trang' ); ?></h2><p><?php esc_html_e( 'Lên thực đơn dễ dàng với rau củ, thịt cá và đồ khô thiết yếu trong cùng một đơn hàng.', 'thuc-pham-thuy-trang' ); ?></p></div><a class="tt-button tt-button--light" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Khám phá thực đơn', 'thuc-pham-thuy-trang' ); ?></a></div></section>

		<section class="tt-featured tt-shell"><div class="tt-section-heading"><div><p class="tt-eyebrow"><?php esc_html_e( 'Gian hàng hôm nay', 'thuc-pham-thuy-trang' ); ?></p><h2><?php esc_html_e( 'Sản phẩm nổi bật', 'thuc-pham-thuy-trang' ); ?></h2></div></div>
		<?php
		$query = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 8, 'meta_key' => '_featured', 'meta_value' => 'yes' ) );
		if ( ! $query->have_posts() ) { $query = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 8 ) ); }
		if ( $query->have_posts() ) { echo '<ul class="products columns-4">'; while ( $query->have_posts() ) { $query->the_post(); wc_get_template_part( 'content', 'product' ); } echo '</ul>'; } else { echo '<p>' . esc_html__( 'Sản phẩm mẫu đang được cập nhật.', 'thuc-pham-thuy-trang' ) . '</p>'; }
		wp_reset_postdata();
		?>
		</section>
	</div>
	<?php
	return ob_get_clean();
}

function tt_replace_front_page_content( $content ) {
	if ( is_front_page() && in_the_loop() && is_main_query() ) { return tt_homepage_markup(); }
	return $content;
}
add_filter( 'the_content', 'tt_replace_front_page_content', 20 );

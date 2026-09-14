<?php
defined( 'ABSPATH' ) || exit;

function tt_homepage_markup() {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/products/' );
	$hero_url = get_stylesheet_directory_uri() . '/assets/images/hero-fresh-market.png';
	$woocommerce_ready = function_exists( 'wc_get_template_part' ) && function_exists( 'wc_get_product' ) && post_type_exists( 'product' );
	$category_url = static function ( $slug ) use ( $shop_url ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		return $term && ! is_wp_error( $term ) ? get_term_link( $term ) : $shop_url;
	};
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

		<section class="tt-weekly"><div class="tt-shell">
			<div class="tt-weekly__header"><div><p class="tt-eyebrow"><?php esc_html_e( 'Gợi ý cho gia đình', 'thuc-pham-thuy-trang' ); ?></p><h2><?php esc_html_e( 'Thực phẩm tươi sạch mỗi tuần', 'thuc-pham-thuy-trang' ); ?></h2><p><?php esc_html_e( 'Lên thực đơn dễ dàng với rau củ, thịt cá và đồ khô thiết yếu trong cùng một đơn hàng.', 'thuc-pham-thuy-trang' ); ?></p></div><a class="tt-button tt-button--light" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Xem tất cả', 'thuc-pham-thuy-trang' ); ?></a></div>
			<div class="tt-weekly-grid">
				<a class="tt-weekly-card tt-weekly-card--green" href="<?php echo esc_url( $category_url( 'rau-cu-qua-tuoi' ) ); ?>"><span><?php esc_html_e( 'Tươi mỗi ngày', 'thuc-pham-thuy-trang' ); ?></span><strong><?php esc_html_e( 'Rau củ theo mùa', 'thuc-pham-thuy-trang' ); ?></strong><small><?php esc_html_e( 'Nhẹ nhàng cho bữa cơm nhà', 'thuc-pham-thuy-trang' ); ?></small></a>
				<a class="tt-weekly-card tt-weekly-card--gold" href="<?php echo esc_url( $category_url( 'thit-heo' ) ); ?>"><span><?php esc_html_e( 'Gợi ý hôm nay', 'thuc-pham-thuy-trang' ); ?></span><strong><?php esc_html_e( 'Thịt tươi dễ chế biến', 'thuc-pham-thuy-trang' ); ?></strong><small><?php esc_html_e( 'Chọn nhanh theo nhu cầu', 'thuc-pham-thuy-trang' ); ?></small></a>
				<a class="tt-weekly-card tt-weekly-card--cream" href="<?php echo esc_url( $category_url( 'do-kho' ) ); ?>"><span><?php esc_html_e( 'Luôn sẵn trong bếp', 'thuc-pham-thuy-trang' ); ?></span><strong><?php esc_html_e( 'Đồ khô tiện lợi', 'thuc-pham-thuy-trang' ); ?></strong><small><?php esc_html_e( 'Dễ bảo quản, dễ kết hợp', 'thuc-pham-thuy-trang' ); ?></small></a>
			</div>
		</div></section>

		<section class="tt-featured tt-shell"><div class="tt-section-heading"><div><p class="tt-eyebrow"><?php esc_html_e( 'Gian hàng hôm nay', 'thuc-pham-thuy-trang' ); ?></p><h2><?php esc_html_e( 'Sản phẩm nổi bật', 'thuc-pham-thuy-trang' ); ?></h2></div></div>
		<?php
		$featured_ids = array();
		if ( $woocommerce_ready ) {
			$query = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 4, 'meta_key' => '_featured', 'meta_value' => 'yes' ) );
			if ( ! $query->have_posts() ) { $query = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 4 ) ); }
			if ( $query->have_posts() ) { echo '<ul class="products columns-4">'; while ( $query->have_posts() ) { $query->the_post(); $featured_ids[] = get_the_ID(); wc_get_template_part( 'content', 'product' ); } echo '</ul>'; } else { echo '<p>' . esc_html__( 'Sản phẩm mẫu đang được cập nhật.', 'thuc-pham-thuy-trang' ) . '</p>'; }
		} else {
			echo '<p>' . esc_html__( 'Sản phẩm đang tạm thời được cập nhật.', 'thuc-pham-thuy-trang' ) . '</p>';
		}
		wp_reset_postdata();
		?>
		</section>

		<section class="tt-category-products tt-shell">
			<div class="tt-section-heading"><div><p class="tt-eyebrow"><?php esc_html_e( 'Đi chợ gọn hơn', 'thuc-pham-thuy-trang' ); ?></p><h2><?php esc_html_e( 'Sản phẩm theo danh mục', 'thuc-pham-thuy-trang' ); ?></h2></div><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Xem tất cả', 'thuc-pham-thuy-trang' ); ?> →</a></div>
			<div class="tt-mini-product-grid">
			<?php
			$more_products = $woocommerce_ready ? new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 6, 'post__not_in' => $featured_ids ) ) : null;
			while ( $more_products && $more_products->have_posts() ) : $more_products->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) { continue; }
				$product_terms = get_the_terms( get_the_ID(), 'product_cat' );
				$category_name = $product_terms && ! is_wp_error( $product_terms ) ? $product_terms[0]->name : '';
				?>
				<article class="tt-mini-product">
					<a class="tt-mini-product__image" href="<?php the_permalink(); ?>"><?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?></a>
					<div><span><?php echo esc_html( $category_name ); ?></span><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><div class="tt-mini-product__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div></div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</section>

		<section class="tt-tips"><div class="tt-shell">
			<div class="tt-section-heading"><div><p class="tt-eyebrow"><?php esc_html_e( 'Mẹo nhỏ mỗi ngày', 'thuc-pham-thuy-trang' ); ?></p><h2><?php esc_html_e( 'Chọn thực phẩm dễ hơn', 'thuc-pham-thuy-trang' ); ?></h2></div></div>
			<div class="tt-tip-grid">
				<article class="tt-tip-card"><span aria-hidden="true">01</span><h3><?php esc_html_e( 'Chọn rau củ tươi', 'thuc-pham-thuy-trang' ); ?></h3><p><?php esc_html_e( 'Ưu tiên rau củ có màu tự nhiên, bề mặt nguyên vẹn và phù hợp với nhu cầu trong tuần.', 'thuc-pham-thuy-trang' ); ?></p></article>
				<article class="tt-tip-card"><span aria-hidden="true">02</span><h3><?php esc_html_e( 'Bảo quản đúng cách', 'thuc-pham-thuy-trang' ); ?></h3><p><?php esc_html_e( 'Phân loại thực phẩm trước khi làm lạnh và dùng hộp kín để giữ hương vị tốt hơn.', 'thuc-pham-thuy-trang' ); ?></p></article>
				<article class="tt-tip-card"><span aria-hidden="true">03</span><h3><?php esc_html_e( 'Lên thực đơn trước', 'thuc-pham-thuy-trang' ); ?></h3><p><?php esc_html_e( 'Chuẩn bị danh sách món giúp mua vừa đủ, tiết kiệm thời gian và hạn chế lãng phí.', 'thuc-pham-thuy-trang' ); ?></p></article>
			</div>
		</div></section>
	</div>
	<?php
	return ob_get_clean();
}

function tt_replace_front_page_content( $content ) {
	if ( is_front_page() && in_the_loop() && is_main_query() ) { return tt_homepage_markup(); }
	return $content;
}
add_filter( 'the_content', 'tt_replace_front_page_content', 20 );

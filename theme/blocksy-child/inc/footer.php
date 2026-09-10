<?php
defined( 'ABSPATH' ) || exit;

function tt_newsletter_redirect( $status ) {
	$url = add_query_arg( 'tt_newsletter', $status, home_url( '/' ) ) . '#tt-newsletter';
	wp_safe_redirect( $url );
	exit;
}

function tt_handle_newsletter_signup() {
	if ( 'POST' !== strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
		tt_newsletter_redirect( 'error' );
	}

	check_admin_referer( 'tt_newsletter_signup', 'tt_newsletter_nonce' );
	if ( ! empty( $_POST['website'] ) ) {
		tt_newsletter_redirect( 'success' );
	}

	$email   = isset( $_POST['newsletter_email'] ) ? sanitize_email( wp_unslash( $_POST['newsletter_email'] ) ) : '';
	$consent = isset( $_POST['newsletter_consent'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['newsletter_consent'] ) );
	if ( ! $consent || ! is_email( $email ) ) {
		tt_newsletter_redirect( 'error' );
	}

	$recipient = tt_store_setting( 'email' );
	if ( ! is_email( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}
	$subject = sprintf( '[%s] Đăng ký nhận bản tin', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$message = sprintf( "Có đăng ký nhận bản tin mới.\nEmail: %s", $email );
	$sent    = wp_mail( $recipient, $subject, $message, array( 'Reply-To: ' . $email ) );
	tt_newsletter_redirect( $sent ? 'success' : 'error' );
}
add_action( 'admin_post_nopriv_tt_newsletter_signup', 'tt_handle_newsletter_signup' );
add_action( 'admin_post_tt_newsletter_signup', 'tt_handle_newsletter_signup' );

function tt_site_footer() {
	$phone         = tt_store_setting( 'phone' );
	$email         = tt_store_setting( 'email' );
	$address       = tt_store_setting( 'address' );
	$weekday_hours = tt_store_setting( 'weekday_hours' );
	$weekend_hours = tt_store_setting( 'weekend_hours' );
	$facebook_url  = tt_store_setting( 'facebook_url' );
	$zalo_url      = tt_store_setting( 'zalo_url' );
	$status        = isset( $_GET['tt_newsletter'] ) ? sanitize_key( wp_unslash( $_GET['tt_newsletter'] ) ) : '';
	$categories    = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => 10,
			'orderby'    => 'name',
		)
	);
	?>
	<footer id="tt-site-footer" class="tt-site-footer" itemscope itemtype="https://schema.org/WPFooter">
		<div class="tt-shell tt-footer-grid">
			<section class="tt-footer-column tt-footer-contact" aria-labelledby="tt-footer-contact-title">
				<h2 id="tt-footer-contact-title"><?php esc_html_e( 'Địa chỉ liên hệ', 'thuc-pham-thuy-trang' ); ?></h2>
				<strong class="tt-footer-brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>
				<?php if ( $address ) : ?><p><?php echo nl2br( esc_html( $address ) ); ?></p><?php endif; ?>
				<?php if ( $phone ) : ?><p><?php esc_html_e( 'Điện thoại:', 'thuc-pham-thuy-trang' ); ?> <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p><?php endif; ?>
				<?php if ( $email ) : ?><p><?php esc_html_e( 'Email:', 'thuc-pham-thuy-trang' ); ?> <a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p><?php endif; ?>
				<?php if ( $weekday_hours || $weekend_hours ) : ?>
					<div class="tt-footer-hours"><strong><?php esc_html_e( 'Giờ làm việc:', 'thuc-pham-thuy-trang' ); ?></strong>
					<?php if ( $weekday_hours ) : ?><span><?php esc_html_e( 'Thứ Hai – Thứ Sáu:', 'thuc-pham-thuy-trang' ); ?> <?php echo esc_html( $weekday_hours ); ?></span><?php endif; ?>
					<?php if ( $weekend_hours ) : ?><span><?php esc_html_e( 'Thứ Bảy & CN:', 'thuc-pham-thuy-trang' ); ?> <?php echo esc_html( $weekend_hours ); ?></span><?php endif; ?></div>
				<?php endif; ?>
				<?php if ( $facebook_url || $zalo_url ) : ?>
					<div class="tt-footer-social"><strong><?php esc_html_e( 'Mạng xã hội:', 'thuc-pham-thuy-trang' ); ?></strong>
					<?php if ( $facebook_url ) : ?><a href="<?php echo esc_url( $facebook_url ); ?>" rel="noopener noreferrer">Facebook</a><?php endif; ?>
					<?php if ( $zalo_url ) : ?><a href="<?php echo esc_url( $zalo_url ); ?>" rel="noopener noreferrer">Zalo</a><?php endif; ?></div>
				<?php endif; ?>
			</section>

			<section class="tt-footer-column" aria-labelledby="tt-footer-categories-title">
				<h2 id="tt-footer-categories-title"><?php esc_html_e( 'Danh mục sản phẩm', 'thuc-pham-thuy-trang' ); ?></h2>
				<ul class="tt-footer-categories">
				<?php if ( ! is_wp_error( $categories ) ) : foreach ( $categories as $category ) : ?>
					<li><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
				<?php endforeach; endif; ?>
				</ul>
			</section>

			<section id="tt-newsletter" class="tt-footer-column tt-footer-newsletter" aria-labelledby="tt-footer-newsletter-title">
				<h2 id="tt-footer-newsletter-title"><?php esc_html_e( 'Bản tin', 'thuc-pham-thuy-trang' ); ?></h2>
				<p><?php esc_html_e( 'Đăng ký nhận bản tin để cập nhật sản phẩm mới và thông tin hữu ích.', 'thuc-pham-thuy-trang' ); ?></p>
				<?php if ( 'success' === $status ) : ?><p class="tt-form-notice is-success" role="status"><?php esc_html_e( 'Cảm ơn bạn đã đăng ký.', 'thuc-pham-thuy-trang' ); ?></p><?php elseif ( 'error' === $status ) : ?><p class="tt-form-notice is-error" role="alert"><?php esc_html_e( 'Chưa thể đăng ký. Vui lòng kiểm tra email và thử lại.', 'thuc-pham-thuy-trang' ); ?></p><?php endif; ?>
				<form class="tt-newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="tt_newsletter_signup">
					<?php wp_nonce_field( 'tt_newsletter_signup', 'tt_newsletter_nonce' ); ?>
					<label class="screen-reader-text" for="tt-newsletter-email"><?php esc_html_e( 'Email của bạn', 'thuc-pham-thuy-trang' ); ?></label>
					<div class="tt-newsletter-row"><input id="tt-newsletter-email" name="newsletter_email" type="email" autocomplete="email" placeholder="<?php esc_attr_e( 'Email của bạn...', 'thuc-pham-thuy-trang' ); ?>" required><button type="submit"><?php esc_html_e( 'Đăng ký', 'thuc-pham-thuy-trang' ); ?></button></div>
					<label class="tt-newsletter-consent"><input type="checkbox" name="newsletter_consent" value="1" required> <span><?php esc_html_e( 'Tôi đồng ý với điều khoản và chính sách bảo mật.', 'thuc-pham-thuy-trang' ); ?></span></label>
					<label class="tt-honeypot" aria-hidden="true">Website <input name="website" type="text" tabindex="-1" autocomplete="off"></label>
				</form>
			</section>
		</div>
		<div class="tt-footer-bottom"><div class="tt-shell"><?php echo esc_html( sprintf( __( 'Bản quyền © %1$s %2$s. Mọi quyền được bảo lưu.', 'thuc-pham-thuy-trang' ), gmdate( 'Y' ), get_bloginfo( 'name' ) ) ); ?></div></div>
	</footer>
	<?php
}
add_action( 'blocksy:footer:before', 'tt_site_footer' );

<?php
defined( 'ABSPATH' ) || exit;

function tt_store_defaults() {
	return array(
		'phone'         => '0967068059',
		'email'         => 'thuytrangfood@gmail.com',
		'address'       => 'Xóm Trung Tâm, Xã Phú Xuyên, Tỉnh Thái Nguyên',
		'weekday_hours' => '',
		'weekend_hours' => '',
		'facebook_url'  => '',
		'zalo_url'      => '',
	);
}

function tt_store_setting( $name ) {
	$defaults = tt_store_defaults();
	if ( ! array_key_exists( $name, $defaults ) ) {
		return '';
	}

	return (string) get_theme_mod( 'tt_' . $name, $defaults[ $name ] );
}

function tt_customize_store_details( $wp_customize ) {
	$wp_customize->add_section(
		'tt_store_details',
		array(
			'title'       => __( 'Thông tin cửa hàng', 'thuc-pham-thuy-trang' ),
			'description' => __( 'Sửa tập trung tên website và thông tin liên hệ hiển thị ở footer.', 'thuc-pham-thuy-trang' ),
			'priority'    => 30,
		)
	);

	foreach ( array( 'blogname', 'blogdescription' ) as $core_setting ) {
		$control = $wp_customize->get_control( $core_setting );
		if ( $control ) {
			$control->section = 'tt_store_details';
		}
	}

	$fields = array(
		'phone'         => array( 'Số điện thoại', 'text', 'sanitize_text_field' ),
		'email'         => array( 'Email liên hệ', 'email', 'sanitize_email' ),
		'address'       => array( 'Địa chỉ', 'textarea', 'sanitize_textarea_field' ),
		'weekday_hours' => array( 'Giờ làm việc Thứ Hai – Thứ Sáu', 'text', 'sanitize_text_field' ),
		'weekend_hours' => array( 'Giờ làm việc Thứ Bảy & CN', 'text', 'sanitize_text_field' ),
		'facebook_url'  => array( 'Liên kết Facebook', 'url', 'esc_url_raw' ),
		'zalo_url'      => array( 'Liên kết Zalo', 'url', 'esc_url_raw' ),
	);

	foreach ( $fields as $name => $field ) {
		$wp_customize->add_setting(
			'tt_' . $name,
			array(
				'default'           => tt_store_defaults()[ $name ],
				'sanitize_callback' => $field[2],
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'tt_' . $name,
			array(
				'label'   => __( $field[0], 'thuc-pham-thuy-trang' ),
				'section' => 'tt_store_details',
				'type'    => $field[1],
			)
		);
	}
}
add_action( 'customize_register', 'tt_customize_store_details' );

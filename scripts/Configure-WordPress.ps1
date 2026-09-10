[CmdletBinding()]
param(
    [string] $ConfigPath = (Join-Path $PSScriptRoot '..\config\local.ps1'),
    [switch] $Apply
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\Project.Common.ps1')
$config = Import-ProjectConfig -Path $ConfigPath
$aboutPath = Join-Path $PSScriptRoot '..\data\pages\gioi-thieu.html'
$contactPath = Join-Path $PSScriptRoot '..\data\pages\lien-he.html'
$articlesPath = Join-Path $PSScriptRoot '..\data\pages\bai-viet.html'
$partnersPath = Join-Path $PSScriptRoot '..\data\pages\doi-tac.html'
foreach ($contentPath in @($aboutPath, $contactPath, $articlesPath, $partnersPath)) {
    if (-not (Test-Path -LiteralPath $contentPath -PathType Leaf)) { throw 'Required page content file is missing.' }
}
$about64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes((Get-Content -Raw -LiteralPath $aboutPath)))
$contact64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes((Get-Content -Raw -LiteralPath $contactPath)))
$articles64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes((Get-Content -Raw -LiteralPath $articlesPath)))
$partners64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes((Get-Content -Raw -LiteralPath $partnersPath)))
if ( -not $Apply ) {
    [pscustomobject]@{ Status = 'DRY-RUN'; Target = $config.ProjectWebRoot; Changes = 'WordPress and WooCommerce desired state' }
    return
}

$php = @'
update_option( 'timezone_string', 'Asia/Ho_Chi_Minh' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'woocommerce_currency', 'VND' );
update_option( 'woocommerce_price_num_decimals', '0' );
update_option( 'woocommerce_default_country', 'VN' );
update_option( 'woocommerce_allowed_countries', 'all' );
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'woocommerce_store_pages_only', 'no' );
update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'title' => 'Thanh toán khi nhận hàng (COD)', 'description' => 'Thanh toán bằng tiền mặt khi nhận hàng.', 'instructions' => 'Vui lòng chuẩn bị đúng số tiền khi nhận hàng.', 'enable_for_methods' => array(), 'enable_for_virtual' => 'yes' ) );
foreach ( array( 'woocommerce_bacs_settings', 'woocommerce_cheque_settings', 'woocommerce_paypal_settings' ) as $key ) {
    $settings = (array) get_option( $key, array() );
    $settings['enabled'] = 'no';
    update_option( $key, $settings );
}
$upsert = static function ( $slug, $title, $content = '' ) {
    $page = get_page_by_path( $slug, OBJECT, 'page' );
    $data = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content );
    if ( $page ) { $data['ID'] = $page->ID; return wp_update_post( $data ); }
    return wp_insert_post( $data );
};
$home_id = $upsert( 'trang-chu', 'Trang chủ' );
$upsert( 'gioi-thieu', 'Giới thiệu', base64_decode( '__ABOUT_CONTENT__' ) );
$upsert( 'lien-he', 'Liên hệ', base64_decode( '__CONTACT_CONTENT__' ) );
$upsert( 'bai-viet', 'Bài viết', base64_decode( '__ARTICLES_CONTENT__' ) );
$upsert( 'doi-tac', 'Đối tác', base64_decode( '__PARTNERS_CONTENT__' ) );
$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : (int) get_option( 'woocommerce_shop_page_id' );
if ( $shop_id > 0 ) { wp_update_post( array( 'ID' => $shop_id, 'post_name' => 'products', 'post_title' => 'Sản phẩm' ) ); }
foreach ( array( 'cart' => 'cart', 'checkout' => 'checkout', 'myaccount' => 'tai-khoan' ) as $key => $slug ) {
    $id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( $key ) : -1;
    if ( $id > 0 ) { wp_update_post( array( 'ID' => $id, 'post_name' => $slug ) ); }
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
flush_rewrite_rules();
echo wp_json_encode( array( 'home' => $home_id, 'shop' => $shop_id ) );
'@
$php = $php.Replace('__ABOUT_CONTENT__', $about64).Replace('__CONTACT_CONTENT__', $contact64).Replace('__ARTICLES_CONTENT__', $articles64).Replace('__PARTNERS_CONTENT__', $partners64)
$encoded = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($php))
$root = $config.ProjectWebRoot
$command = "wp eval 'eval(base64_decode(`"$encoded`"));' --path='$root' --allow-root"
$output = Invoke-ProjectSsh -Config $config -Command $command
[pscustomobject]@{ Status = 'APPLIED'; Result = ($output -join '') }

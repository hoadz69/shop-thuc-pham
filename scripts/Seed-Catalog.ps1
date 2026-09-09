[CmdletBinding()]
param(
    [string] $ConfigPath = (Join-Path $PSScriptRoot '..\config\local.ps1'),
    [switch] $Apply
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\Project.Common.ps1')
$config = Import-ProjectConfig -Path $ConfigPath
$categories = @(Import-Csv (Join-Path $PSScriptRoot '..\data\categories.csv'))
$products = @(Import-Csv (Join-Path $PSScriptRoot '..\data\products.csv'))
if ($categories.Count -ne 7 -or $products.Count -ne 12) { throw 'Catalog fixture count is invalid.' }
if ( -not $Apply ) { [pscustomobject]@{ Status='DRY-RUN'; Categories=$categories.Count; Products=$products.Count }; return }

$remoteImage = "/tmp/tt-product-placeholder-$([guid]::NewGuid().ToString('N')).png"
$localImage = Join-Path $PSScriptRoot '..\theme\blocksy-child\assets\images\product-placeholder.png'
Copy-ProjectScp -Config $config -SourcePath $localImage -DestinationPath $remoteImage | Out-Null
try {
    $root = $config.ProjectWebRoot
    $findImage = "wp post list --post_type=attachment --meta_key=_tt_seed_asset --meta_value=product-placeholder --field=ID --format=ids --path='$root' --allow-root"
    $imageId = ((Invoke-ProjectSsh -Config $config -Command $findImage) -join '').Trim()
    if ($imageId -notmatch '^[0-9]+$') {
        $imageId = ((Invoke-ProjectSsh -Config $config -Command "wp media import '$remoteImage' --title='Ảnh minh họa sản phẩm mẫu' --porcelain --path='$root' --allow-root") -join '').Trim()
        if ($imageId -notmatch '^[0-9]+$') { throw 'Could not create the sample product image attachment.' }
        Invoke-ProjectSsh -Config $config -Command "wp post meta set '$imageId' _tt_seed_asset product-placeholder --path='$root' --allow-root" | Out-Null
    }

    $payload = [ordered]@{ categories=$categories; products=$products; imageId=[int]$imageId } | ConvertTo-Json -Depth 5 -Compress
    $payload64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($payload))
    $php = @'
$data = json_decode( base64_decode( '__PAYLOAD__' ), true );
$term_ids = array();
foreach ( $data['categories'] as $category ) {
    $existing = term_exists( $category['slug'], 'product_cat' );
    if ( ! $existing ) { $existing = wp_insert_term( $category['name'], 'product_cat', array( 'slug' => $category['slug'] ) ); }
    if ( is_wp_error( $existing ) ) { throw new Exception( $existing->get_error_message() ); }
    $term_ids[$category['slug']] = (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
}
foreach ( $data['products'] as $row ) {
    $id = wc_get_product_id_by_sku( $row['sku'] );
    $product = $id ? new WC_Product_Simple( $id ) : new WC_Product_Simple();
    $product->set_name( $row['name'] );
    $product->set_slug( $row['slug'] );
    $product->set_sku( $row['sku'] );
    $product->set_status( 'publish' );
    $product->set_catalog_visibility( 'visible' );
    $product->set_regular_price( (string) (int) $row['regular_price'] );
    $product->set_price( (string) (int) $row['regular_price'] );
    $product->set_short_description( $row['short_description'] );
    $product->set_description( $row['description'] );
    $product->set_category_ids( array( $term_ids[$row['category_slug']] ) );
    $product->set_image_id( (int) $data['imageId'] );
    $product->set_featured( 'yes' === $row['featured'] );
    $product->update_meta_data( '_tt_unit', $row['unit'] );
    $product->save();
}
echo wp_json_encode( array( 'categories' => count( $term_ids ), 'products' => count( $data['products'] ) ) );
'@
    $php = $php.Replace('__PAYLOAD__', $payload64)
    $code64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($php))
    $result = Invoke-ProjectSsh -Config $config -Command "wp eval 'eval(base64_decode(`"$code64`"));' --path='$root' --allow-root"
}
finally {
    Invoke-ProjectSsh -Config $config -Command "rm -f -- '$remoteImage'" | Out-Null
}
[pscustomobject]@{ Status='APPLIED'; Result=($result -join '') }

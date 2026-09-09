Describe 'Idempotent WordPress desired state' {
    BeforeAll { $source = Get-Content -Raw (Join-Path $PSScriptRoot '..\..\scripts\Configure-WordPress.ps1') }
    It 'sets locale-related store options and COD' { foreach($value in @('Asia/Ho_Chi_Minh','/%postname%/','VND','woocommerce_price_num_decimals','woocommerce_default_country','VN','woocommerce_cod_settings')) { $source | Should -Match ([regex]::Escape($value)) } }
    It 'publishes the storefront instead of leaving WooCommerce coming-soon mode enabled' { $source | Should -Match "woocommerce_coming_soon', 'no"; $source | Should -Match "woocommerce_store_pages_only', 'no" }
    It 'disables non-COD gateways' { foreach($gateway in @('bacs','cheque','paypal')) { $source | Should -Match "woocommerce_${gateway}_settings" } }
    It 'upserts pages by slug and configures products as the shop slug' { foreach($slug in @('products','trang-chu','gioi-thieu','lien-he')) { $source | Should -Match $slug }; $source | Should -Match 'get_page_by_path' }
    It 'requires Apply before making remote changes' { $source | Should -Match '\[switch\] \$Apply'; $source | Should -Match 'if \( -not \$Apply \)' }
}

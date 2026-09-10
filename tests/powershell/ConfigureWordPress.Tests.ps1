Describe 'Idempotent WordPress desired state' {
    BeforeAll {
        $projectRoot = Join-Path $PSScriptRoot '..\..'
        $source = Get-Content -Raw (Join-Path $projectRoot 'scripts\Configure-WordPress.ps1')
        $pageContent = (Get-Content -Raw (Join-Path $projectRoot 'data\pages\gioi-thieu.html')) + (Get-Content -Raw (Join-Path $projectRoot 'data\pages\lien-he.html')) + (Get-Content -Raw (Join-Path $projectRoot 'data\pages\bai-viet.html')) + (Get-Content -Raw (Join-Path $projectRoot 'data\pages\doi-tac.html'))
    }
    It 'sets locale-related store options and COD' { foreach($value in @('Asia/Ho_Chi_Minh','/%postname%/','VND','woocommerce_price_num_decimals','woocommerce_default_country','VN','woocommerce_cod_settings')) { $source | Should -Match ([regex]::Escape($value)) } }
    It 'publishes the storefront instead of leaving WooCommerce coming-soon mode enabled' { $source | Should -Match "woocommerce_coming_soon', 'no"; $source | Should -Match "woocommerce_store_pages_only', 'no" }
    It 'disables non-COD gateways' { foreach($gateway in @('bacs','cheque','paypal')) { $source | Should -Match "woocommerce_${gateway}_settings" } }
    It 'upserts pages by slug and configures products as the shop slug' { foreach($slug in @('products','trang-chu','bai-viet','doi-tac','gioi-thieu','lien-he')) { $source | Should -Match $slug }; $source | Should -Match 'get_page_by_path' }
    It 'loads version-controlled editable page content' { foreach($page in @('gioi-thieu','lien-he','bai-viet','doi-tac')) { $source | Should -Match "data\\pages\\$page\.html" }; foreach($token in @('__ABOUT_CONTENT__','__CONTACT_CONTENT__','__ARTICLES_CONTENT__','__PARTNERS_CONTENT__')) { $source | Should -Match $token } }
    It 'does not invent contact details or embed executable content' { $pageContent | Should -Not -Match '(?i)<script|<iframe'; $pageContent | Should -Not -Match '[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}'; $pageContent | Should -Not -Match '(?:\+?84|0)[0-9 .-]{8,}' }
    It 'requires Apply before making remote changes' { $source | Should -Match '\[switch\] \$Apply'; $source | Should -Match 'if \( -not \$Apply \)' }
}

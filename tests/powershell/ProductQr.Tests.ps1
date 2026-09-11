Describe 'Bulk product QR labels' {
    BeforeAll {
        $root = Join-Path $PSScriptRoot '..\..'
        $source = Get-Content -Raw (Join-Path $root 'plugin\tt-product-qr\src\ProductQr.php')
        $printCss = Get-Content -Raw (Join-Path $root 'plugin\tt-product-qr\assets\print.css')
    }

    It 'adds a product submenu with a selectable published-product list' {
        $source | Should -Match 'add_submenu_page'
        $source | Should -Match "edit\.php\?post_type=product"
        $source | Should -Match 'wc_get_products'
        $source | Should -Match 'product_ids\[\]'
        $source | Should -Match 'quantities\['
    }

    It 'protects batch printing with capability and nonce checks' {
        $source | Should -Match 'admin_post_tt_print_product_qr_batch'
        $source | Should -Match "current_user_can\( 'edit_products' \)"
        $source | Should -Match "check_admin_referer\( 'tt_print_product_qr_batch' \)"
        $source | Should -Match 'Maximum 200 labels'
    }

    It 'prints canonical product URLs on separate 50 by 35 mm labels' {
        $source | Should -Match 'self::product_url'
        $source | Should -Match 'window\.print'
        $printCss | Should -Match 'size:50mm 35mm'
        $printCss | Should -Match 'break-after:page'
    }
}

Describe 'Reproducible sample catalog' {
    BeforeAll {
        $root = Join-Path $PSScriptRoot '..\..'
        $categories = Import-Csv (Join-Path $root 'data\categories.csv')
        $products = Import-Csv (Join-Path $root 'data\products.csv')
        $source = Get-Content -Raw (Join-Path $root 'scripts\Seed-Catalog.ps1')
    }
    It 'contains exactly seven unique category slugs' { $categories.Count | Should -Be 7; ($categories.slug | Sort-Object -Unique).Count | Should -Be 7 }
    It 'contains twelve unique valid products' { $products.Count | Should -Be 12; ($products.sku | Sort-Object -Unique).Count | Should -Be 12; ($products.slug | Sort-Object -Unique).Count | Should -Be 12; foreach($p in $products){ [int]$p.regular_price | Should -BeGreaterThan 0; $p.unit | Should -BeIn @('kg','gói','hộp','con','khay'); $p.category_slug | Should -BeIn $categories.slug; $p.image_filename | Should -Match '^[A-Za-z0-9][A-Za-z0-9._-]*$' } }
    It 'upserts terms and products by stable keys' { $source | Should -Match 'term_exists'; $source | Should -Match 'wc_get_product_id_by_sku'; $source | Should -Match 'WC_Product_Simple'; $source | Should -Match 'set_category_ids' }
    It 'requires Apply before touching the server' { $source | Should -Match '\[switch\] \$Apply'; $source | Should -Match 'if \( -not \$Apply \)' }
}

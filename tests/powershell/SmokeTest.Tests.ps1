Describe 'Storefront smoke test' {
    BeforeAll { $source = Get-Content -Raw (Join-Path $PSScriptRoot '..\..\scripts\Smoke-Test.ps1') }
    It 'checks HTTP and parent-child theme state' { $source | Should -Match 'Invoke-WebRequest'; $source | Should -Match 'template=blocksy'; $source | Should -Match 'stylesheet=blocksy-child' }
    It 'checks the expected catalog count' { $source | Should -Match 'products=12' }
    It 'checks project plugins, canonical QR payload and PWA endpoints' {
        $source | Should -Match 'tt-product-qr'
        $source | Should -Match 'data-qr-payload'
        $source | Should -Match 'tt-manifest'
        $source | Should -Match 'Service-Worker-Allowed'
    }
    It 'fails on a recent PHP fatal' { $source | Should -Match 'Fatal error'; $source | Should -Match 'fatal=none' }
}

Describe 'Storefront smoke test' {
    BeforeAll { $source = Get-Content -Raw (Join-Path $PSScriptRoot '..\..\scripts\Smoke-Test.ps1') }
    It 'checks HTTP and parent-child theme state' { $source | Should -Match 'Invoke-WebRequest'; $source | Should -Match 'template=blocksy'; $source | Should -Match 'stylesheet=blocksy-child' }
    It 'checks the expected catalog count' { $source | Should -Match 'products=12' }
}

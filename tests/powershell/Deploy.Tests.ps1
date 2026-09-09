Describe 'Scoped deploy' {
    BeforeAll { $source = Get-Content -Raw (Join-Path $PSScriptRoot '..\..\scripts\Deploy.ps1') }
    It 'targets only the custom theme or plugin paths' { $source | Should -Match 'wp-content/themes'; $source | Should -Match 'wp-content/plugins'; $source | Should -Not -Match 'wp-admin|wp-includes' }
    It 'rejects links and creates rollback archives' { $source | Should -Match 'ReparsePoint'; $source | Should -Match 'type l'; $source | Should -Match 'rollback=' }
    It 'sets ownership and scoped permissions before activation' { $source.IndexOf('chown -R www:www') | Should -BeLessThan $source.IndexOf('wp $($spec.Activate) activate'); $source | Should -Match 'chmod 775'; $source | Should -Match 'chmod 664' }
}

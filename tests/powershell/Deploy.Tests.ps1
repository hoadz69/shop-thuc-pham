Describe 'Scoped deploy' {
    BeforeAll { $source = Get-Content -Raw (Join-Path $PSScriptRoot '..\..\scripts\Deploy.ps1') }
    It 'targets only the custom theme or plugin paths' { $source | Should -Match 'wp-content/themes'; $source | Should -Match 'wp-content/plugins'; $source | Should -Not -Match 'wp-admin|wp-includes' }
    It 'rejects links and creates timestamped rollback archives' {
        $source | Should -Match 'ReparsePoint'
        $source | Should -Match 'type l'
        $source | Should -Match 'rollback="/www/backup/site/.+`\$\(date -u \+%Y%m%dT%H%M%SZ\)'
        $source | Should -Not -Match "rollback='/www/backup/site/.+`\$\(date"
    }
    It 'keeps deployed code read-only to the PHP-FPM user before activation' { $source.IndexOf('chown -R root:www') | Should -BeLessThan $source.IndexOf('wp $($spec.Activate) activate'); $source | Should -Match 'chmod 755'; $source | Should -Match 'chmod 644'; $source | Should -Not -Match 'chown -R www:www' }
}

Describe 'WordPress Nginx security hardening' {
    BeforeAll {
        $source = Get-Content -Raw (Join-Path $PSScriptRoot '..\..\config\nginx-wordpress-security.conf')
    }

    It 'blocks executable PHP variants below uploads' {
        $source | Should -Match '\^/wp-content/uploads/'
        $source | Should -Match 'php\[0-9\]\?\|phtml\|phar'
        $source | Should -Match 'return 403;'
    }

    It 'does not alter the WordPress application routes' {
        $source | Should -Not -Match 'wp-admin|wp-includes|try_files|rewrite'
    }
}

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

Describe 'OpenSSH key-only hardening' {
    BeforeAll {
        $source = Get-Content -Raw (Join-Path $PSScriptRoot '..\..\config\sshd-key-only.conf')
    }

    It 'keeps the project SSH port outside this authentication-only drop-in' {
        $source | Should -Not -Match '(?m)^\s*Port\s+'
    }

    It 'allows public keys while rejecting password and keyboard authentication' {
        $source | Should -Match '(?m)^PubkeyAuthentication yes$'
        $source | Should -Match '(?m)^PasswordAuthentication no$'
        $source | Should -Match '(?m)^KbdInteractiveAuthentication no$'
        $source | Should -Match '(?m)^PermitRootLogin prohibit-password$'
        $source | Should -Not -Match '(?m)^PasswordAuthentication yes$'
    }
}

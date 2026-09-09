Describe 'Verified WP-CLI installer' {
    BeforeAll {
        $script:path = Join-Path $PSScriptRoot '..\..\scripts\Install-WpCli.ps1'
        $script:source = if (Test-Path $script:path) { Get-Content -Raw $script:path } else { '' }
    }

    It 'downloads the phar, signature, and signing key from the official build repository' {
        $script:source | Should -Match 'raw\.githubusercontent\.com/wp-cli/builds/gh-pages/phar/wp-cli\.phar'
        $script:source | Should -Match 'wp-cli\.phar\.asc'
        $script:source | Should -Match 'raw\.githubusercontent\.com/wp-cli/builds/gh-pages/wp-cli\.pgp'
    }

    It 'verifies before installing outside the web root' {
        $script:source.IndexOf('gpg --verify') | Should -BeLessThan $script:source.IndexOf("install -m 0755")
        $script:source | Should -Match '/usr/local/bin/wp'
        $script:source | Should -Not -Match 'wp-content|wp-admin|wp-includes'
    }

    It 'uses a temporary keyring and fails closed' {
        $script:source | Should -Match 'set -Eeuo pipefail'
        $script:source | Should -Match 'GNUPGHOME'
        $script:source | Should -Match 'mktemp -d'
        $script:source | Should -Match "trap 'rm -rf --"
    }
}

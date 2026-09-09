Describe 'Read-only server preflight and inventory' {
    BeforeAll {
        $script:preflightPath = Join-Path $PSScriptRoot '..\..\scripts\Test-Connection.ps1'
        $script:inventoryPath = Join-Path $PSScriptRoot '..\..\scripts\Get-ServerInventory.ps1'
        function Import-ProjectConfig {
            [pscustomobject]@{
                ProjectServerHost = 'server.example.test'
                ProjectSshPort = 2222
                ProjectSshUser = 'root'
                ProjectSshKeyPath = 'C:\fixture.ppk'
                ProjectHostKey = 'SHA256:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                ProjectWebRoot = '/www/wwwroot/test-site'
            }
        }

        function Invoke-ProjectSsh {
            param($Config, $Command)
            $global:TestInventoryCommands.Add($Command)
            if ($Command -match 'base64_decode') {
                return '{"wordpressVersion":"6.8.2","siteUrl":"http://example.test","homeUrl":"http://example.test","activeTheme":"twentytwentyfive","themes":["twentytwentyfive"],"plugins":[{"name":"woocommerce","active":true}],"counts":{"products":0,"orders":0,"pages":5},"uploadFiles":12}'
            }
            return @(
                'hostname=test-host'
                'uid=0'
                'disk_available_kb=40000000'
                'web_root_exists=true'
                'owner=www'
                'group=www'
                'php=/usr/bin/php'
                'mysql=/usr/bin/mysql'
                'mysqldump=/usr/bin/mysqldump'
                'tar=/usr/bin/tar'
                'gzip=/usr/bin/gzip'
                'sha256sum=/usr/bin/sha256sum'
                'wp=missing'
            )
        }
    }

    BeforeEach {
        $global:TestInventoryCommands = [System.Collections.Generic.List[string]]::new()
    }

    It 'preflight checks identity, free space, root metadata, and required binaries' {
        (& $script:preflightPath -ConfigPath 'fixture.ps1' | Out-String) | Should -Match 'test-host'
        $joined = $global:TestInventoryCommands -join "`n"
        $joined | Should -Match 'hostname'
        $joined | Should -Match 'id -u'
        $joined | Should -Match 'df -P'
        $joined | Should -Match 'stat'
        $joined | Should -Match 'command -v'
    }

    It 'collects WordPress, theme, plugin, content, and upload inventory as JSON' {
        $json = & $script:inventoryPath -ConfigPath 'fixture.ps1'
        { $json | ConvertFrom-Json } | Should -Not -Throw
        $parsed = $json | ConvertFrom-Json
        $parsed.wordpressVersion | Should -Be '6.8.2'

        $joined = $global:TestInventoryCommands -join "`n"
        $joined | Should -Match 'df -P'
        $joined | Should -Match 'stat'
        $joined | Should -Match 'wp-includes/version.php'
        $joined | Should -Match 'wp_get_themes'
        $joined | Should -Match 'get_plugins'
        $joined | Should -Match 'product'
        $joined | Should -Match 'shop_order'
        $joined | Should -Match 'page'
        $joined | Should -Match 'uploads'
    }

    It 'uses no mutating command in either read-only script' {
        & $script:preflightPath -ConfigPath 'fixture.ps1' | Out-Null
        & $script:inventoryPath -ConfigPath 'fixture.ps1' | Out-Null
        $joined = $global:TestInventoryCommands -join "`n"
        $joined | Should -Not -Match '(?m)(^|[;&|]\s*)rm\s'
        $joined | Should -Not -Match '(?m)(^|[;&|]\s*)mv\s'
        $joined | Should -Not -Match 'sed\s+-i'
        $joined | Should -Not -Match 'wp\s+option\s+update'
        $joined | Should -Not -Match 'wp\s+plugin\s+install'
    }
}

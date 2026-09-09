Describe 'Import-ProjectConfig' {
    BeforeAll {
        $libraryPath = Join-Path $PSScriptRoot '..\..\scripts\lib\Project.Common.ps1'
        if (Test-Path -LiteralPath $libraryPath -PathType Leaf) {
            . $libraryPath
        }

        function New-TestProjectConfig {
            param(
                [Parameter(Mandatory = $true)]
                [string] $Path,

                [Parameter(Mandatory = $true)]
                [string] $KeyPath,

                [hashtable] $Overrides = @{},

                [string[]] $Omit = @()
            )

            $values = [ordered]@{
                ProjectServerHost = '127.0.0.1'
                ProjectSshPort = 2222
                ProjectSshUser = 'root'
                ProjectSshKeyPath = $KeyPath
                ProjectHostKey = 'SHA256:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                ProjectWebRoot = '/www/wwwroot/test-site'
            }

            foreach ($name in $Overrides.Keys) {
                $values[$name] = $Overrides[$name]
            }

            $lines = foreach ($name in $values.Keys) {
                if ($Omit -contains $name) {
                    continue
                }

                $value = $values[$name]
                if ($value -is [string]) {
                    '$' + $name + " = '" + $value.Replace("'", "''") + "'"
                }
                else {
                    '$' + $name + ' = ' + [string] $value
                }
            }

            Set-Content -LiteralPath $Path -Value $lines -Encoding UTF8
        }

        function Get-ImportErrorMessage {
            param(
                [Parameter(Mandatory = $true)]
                [string] $Path
            )

            try {
                Import-ProjectConfig -Path $Path | Out-Null
                return $null
            }
            catch {
                return $_.Exception.Message
            }
        }
    }

    BeforeEach {
        $script:keyPath = Join-Path $TestDrive 'test-key.ppk'
        Set-Content -LiteralPath $script:keyPath -Value 'test fixture, not a private key' -Encoding UTF8
        $script:configPath = Join-Path $TestDrive 'local.ps1'
    }

    It 'rejects a config without ProjectServerHost' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Omit 'ProjectServerHost'
        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectServerHost'
    }

    It 'rejects a config without ProjectSshPort' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Omit 'ProjectSshPort'
        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectSshPort'
    }

    It 'rejects a config without ProjectSshUser' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Omit 'ProjectSshUser'
        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectSshUser'
    }

    It 'rejects a config without ProjectSshKeyPath' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Omit 'ProjectSshKeyPath'
        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectSshKeyPath'
    }

    It 'rejects a config without ProjectHostKey' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Omit 'ProjectHostKey'
        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectHostKey'
    }

    It 'rejects a config without ProjectWebRoot' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Omit 'ProjectWebRoot'
        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectWebRoot'
    }

    It 'does not inherit a missing value from the caller scope' {
        $ProjectHostKey = 'SHA256:caller-scope-value'
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Omit 'ProjectHostKey'

        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectHostKey'
    }

    It 'rejects a broad web root' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Overrides @{
            ProjectWebRoot = '/'
        }

        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectWebRoot'
    }

    It 'rejects a nested web root' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Overrides @{
            ProjectWebRoot = '/www/wwwroot/site/nested'
        }

        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectWebRoot'
    }

    It 'rejects unsafe web-root segments' {
        $unsafeRoots = @(
            '/www/wwwroot/.'
            '/www/wwwroot/..'
            '/www/wwwroot/site name'
            "/www/wwwroot/site`nname"
            '/www/wwwroot/site;id'
            '/www/wwwroot/site$(id)'
            '/www/wwwroot/site`id`'
        )

        foreach ($unsafeRoot in $unsafeRoots) {
            New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Overrides @{
                ProjectWebRoot = $unsafeRoot
            }

            Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectWebRoot'
        }
    }

    It 'rejects an SSH port other than 2222' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Overrides @{
            ProjectSshPort = 22
        }

        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectSshPort.*2222'
    }

    It 'rejects a key path that is not an existing file' {
        $missingKeyPath = Join-Path $TestDrive 'missing-key.ppk'
        New-TestProjectConfig -Path $script:configPath -KeyPath $missingKeyPath

        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectSshKeyPath'
    }

    It 'rejects a host fingerprint without the SHA256 prefix' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Overrides @{
            ProjectHostKey = 'legacy-fingerprint'
        }

        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectHostKey.*SHA256:'
    }

    It 'rejects a malformed SHA256 host fingerprint' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath -Overrides @{
            ProjectHostKey = 'SHA256:too-short'
        }

        Get-ImportErrorMessage -Path $script:configPath | Should -Match 'ProjectHostKey.*SHA256:'
    }

    It 'returns exactly the six supported public fields for a valid config' {
        New-TestProjectConfig -Path $script:configPath -KeyPath $script:keyPath

        $config = Import-ProjectConfig -Path $script:configPath
        $actualNames = @($config.PSObject.Properties.Name | Sort-Object)
        $expectedNames = @(
            'ProjectHostKey',
            'ProjectServerHost',
            'ProjectSshKeyPath',
            'ProjectSshPort',
            'ProjectSshUser',
            'ProjectWebRoot'
        )

        ($actualNames -join ',') | Should -Be ($expectedNames -join ',')
        $config.ProjectSshPort | Should -Be 2222
    }
}

Describe 'PuTTY connection helpers' {
    BeforeAll {
        $libraryPath = Join-Path $PSScriptRoot '..\..\scripts\lib\Project.Common.ps1'
        if (Test-Path -LiteralPath $libraryPath -PathType Leaf) {
            . $libraryPath
        }
    }

    BeforeEach {
        $script:connectionConfig = [pscustomobject]@{
            ProjectServerHost = 'server.example.test'
            ProjectSshPort = 2222
            ProjectSshUser = 'deploy-user'
            ProjectSshKeyPath = 'C:\keys\project key.ppk'
            ProjectHostKey = 'SHA256:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            ProjectWebRoot = '/www/wwwroot/site'
        }
        $script:plinkArguments = @()
        $script:pscpArguments = @()

        Mock plink {
            $script:plinkArguments = @($args)
            $global:LASTEXITCODE = 0
            'remote-output'
        }
        Mock pscp {
            $script:pscpArguments = @($args)
            $global:LASTEXITCODE = 0
            'copy-output'
        }
    }

    It 'passes SSH options as discrete arguments and pins the host fingerprint' {
        $output = Invoke-ProjectSsh -Config $script:connectionConfig -Command 'printf safe'

        ($script:plinkArguments -join '|') | Should -Be '-batch|-P|2222|-i|C:\keys\project key.ppk|-hostkey|SHA256:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA|deploy-user@server.example.test|printf safe'
        ($output -join '|') | Should -Be 'remote-output'
    }

    It 'builds an upload target without flattening the argument array' {
        $output = Copy-ProjectScp -Config $script:connectionConfig -SourcePath 'C:\build\site archive.zip' -DestinationPath '/tmp/site archive.zip'

        ($script:pscpArguments -join '|') | Should -Be '-batch|-P|2222|-i|C:\keys\project key.ppk|-hostkey|SHA256:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA|C:\build\site archive.zip|deploy-user@server.example.test:/tmp/site archive.zip'
        ($output -join '|') | Should -Be 'copy-output'
    }

    It 'builds a recursive download source with the pinned fingerprint' {
        Copy-ProjectScp -Config $script:connectionConfig -SourcePath '/www/backup/site/snapshot' -DestinationPath 'C:\backups\snapshot' -FromRemote -Recurse | Out-Null

        ($script:pscpArguments -join '|') | Should -Be '-batch|-P|2222|-i|C:\keys\project key.ppk|-hostkey|SHA256:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA|-r|deploy-user@server.example.test:/www/backup/site/snapshot|C:\backups\snapshot'
    }

    It 'throws a secret-safe error when plink exits nonzero' {
        $secretCommand = 'printf command-sensitive-value'
        Mock plink {
            $global:LASTEXITCODE = 17
        }

        $message = try {
            Invoke-ProjectSsh -Config $script:connectionConfig -Command $secretCommand | Out-Null
            $null
        }
        catch {
            $_.Exception.Message
        }

        $message | Should -Be 'plink exited with code 17.'
        $message | Should -Not -Match ([regex]::Escape($secretCommand))
        $message | Should -Not -Match ([regex]::Escape($script:connectionConfig.ProjectSshKeyPath))
        $message | Should -Not -Match ([regex]::Escape($script:connectionConfig.ProjectHostKey))
    }

    It 'throws a secret-safe error when pscp exits nonzero' {
        $sensitiveSource = 'C:\private\sensitive archive.zip'
        Mock pscp {
            $global:LASTEXITCODE = 23
        }

        $message = try {
            Copy-ProjectScp -Config $script:connectionConfig -SourcePath $sensitiveSource -DestinationPath '/tmp/upload.zip' | Out-Null
            $null
        }
        catch {
            $_.Exception.Message
        }

        $message | Should -Be 'pscp exited with code 23.'
        $message | Should -Not -Match ([regex]::Escape($sensitiveSource))
        $message | Should -Not -Match ([regex]::Escape($script:connectionConfig.ProjectSshKeyPath))
        $message | Should -Not -Match ([regex]::Escape($script:connectionConfig.ProjectHostKey))
    }
}

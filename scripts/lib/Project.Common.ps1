function Import-ProjectConfig {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [string] $Path
    )

    $resolvedPath = Resolve-Path -LiteralPath $Path -ErrorAction Stop
    $rawConfig = & {
        param([string] $ConfigPath)

        $ProjectServerHost = $null
        $ProjectSshPort = $null
        $ProjectSshUser = $null
        $ProjectSshKeyPath = $null
        $ProjectHostKey = $null
        $ProjectWebRoot = $null

        . $ConfigPath

        [pscustomobject][ordered]@{
            ProjectServerHost = $ProjectServerHost
            ProjectSshPort = $ProjectSshPort
            ProjectSshUser = $ProjectSshUser
            ProjectSshKeyPath = $ProjectSshKeyPath
            ProjectHostKey = $ProjectHostKey
            ProjectWebRoot = $ProjectWebRoot
        }
    } $resolvedPath.Path

    foreach ($name in @(
        'ProjectServerHost',
        'ProjectSshPort',
        'ProjectSshUser',
        'ProjectSshKeyPath',
        'ProjectHostKey',
        'ProjectWebRoot'
    )) {
        $value = $rawConfig.$name
        if ($null -eq $value -or ($value -is [string] -and [string]::IsNullOrWhiteSpace($value))) {
            throw "Required configuration value '$name' is missing."
        }
    }

    if ($rawConfig.ProjectSshPort -ne 2222) {
        throw "ProjectSshPort must be exactly 2222."
    }

    if (-not (Test-Path -LiteralPath ([string] $rawConfig.ProjectSshKeyPath) -PathType Leaf)) {
        throw 'ProjectSshKeyPath must point to an existing key file.'
    }

    if ([string] $rawConfig.ProjectHostKey -cnotmatch '^SHA256:[A-Za-z0-9+/]{43}\z') {
        throw 'ProjectHostKey must be a SHA256: fingerprint with a 43-character Base64 digest.'
    }

    $webRoot = [string] $rawConfig.ProjectWebRoot
    if (
        $webRoot -in @('/www/wwwroot/.', '/www/wwwroot/..') -or
        $webRoot -notmatch '^/www/wwwroot/[A-Za-z0-9][A-Za-z0-9._-]*\z'
    ) {
        throw 'ProjectWebRoot must identify one site directly below /www/wwwroot.'
    }

    [pscustomobject][ordered]@{
        ProjectServerHost = [string] $rawConfig.ProjectServerHost
        ProjectSshPort = [int] $rawConfig.ProjectSshPort
        ProjectSshUser = [string] $rawConfig.ProjectSshUser
        ProjectSshKeyPath = (Resolve-Path -LiteralPath ([string] $rawConfig.ProjectSshKeyPath)).Path
        ProjectHostKey = [string] $rawConfig.ProjectHostKey
        ProjectWebRoot = [string] $rawConfig.ProjectWebRoot
    }
}

function Invoke-ProjectSsh {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [psobject] $Config,

        [Parameter(Mandatory = $true)]
        [Alias('RemoteCommand')]
        [string] $Command
    )

    $arguments = @(
        '-batch'
        '-P'
        [string] $Config.ProjectSshPort
        '-i'
        [string] $Config.ProjectSshKeyPath
        '-hostkey'
        [string] $Config.ProjectHostKey
        "$($Config.ProjectSshUser)@$($Config.ProjectServerHost)"
        $Command
    )

    & plink @arguments
    $exitCode = $LASTEXITCODE
    if ($exitCode -ne 0) {
        throw "plink exited with code ${exitCode}."
    }
}

function Copy-ProjectScp {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [psobject] $Config,

        [Parameter(Mandatory = $true)]
        [string] $SourcePath,

        [Parameter(Mandatory = $true)]
        [string] $DestinationPath,

        [switch] $FromRemote,

        [switch] $Recurse
    )

    $arguments = @(
        '-batch'
        '-P'
        [string] $Config.ProjectSshPort
        '-i'
        [string] $Config.ProjectSshKeyPath
        '-hostkey'
        [string] $Config.ProjectHostKey
    )

    if ($Recurse) {
        $arguments += '-r'
    }

    $remoteEndpoint = "$($Config.ProjectSshUser)@$($Config.ProjectServerHost)"
    if ($FromRemote) {
        $arguments += "${remoteEndpoint}:$SourcePath"
        $arguments += $DestinationPath
    }
    else {
        $arguments += $SourcePath
        $arguments += "${remoteEndpoint}:$DestinationPath"
    }

    & pscp @arguments
    $exitCode = $LASTEXITCODE
    if ($exitCode -ne 0) {
        throw "pscp exited with code ${exitCode}."
    }
}

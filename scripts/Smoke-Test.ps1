[CmdletBinding()]
param(
    [ValidateSet('Theme', 'Catalog', 'All')]
    [string] $Scope = 'Theme',
    [string] $ConfigPath = (Join-Path $PSScriptRoot '..\config\local.ps1'),
    [string] $BaseUrl
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\Project.Common.ps1')
$config = Import-ProjectConfig -Path $ConfigPath
if (-not $BaseUrl) { $BaseUrl = "http://$($config.ProjectServerHost)" }

$paths = if ($Scope -eq 'Theme') { @('/') } else { @('/', '/products/', '/cart/', '/checkout/') }
foreach ($path in $paths) {
    $response = Invoke-WebRequest -Uri ($BaseUrl.TrimEnd('/') + $path) -MaximumRedirection 5 -TimeoutSec 30
    if ($response.StatusCode -ne 200) { throw "HTTP smoke failed: $path" }
}

$root = $config.ProjectWebRoot
if ($Scope -in @('Theme', 'All', 'Catalog')) {
    $state = @(Invoke-ProjectSsh -Config $config -Command "printf 'template='; wp option get template --path='$root' --allow-root; printf 'stylesheet='; wp option get stylesheet --path='$root' --allow-root; printf 'products='; wp post list --post_type=product --post_status=publish --format=count --path='$root' --allow-root")
    $joined = $state -join "`n"
    if ($joined -notmatch 'template=blocksy' -or $joined -notmatch 'stylesheet=blocksy-child') { throw 'Theme smoke test failed.' }
    if ($Scope -ne 'Theme' -and $joined -notmatch 'products=12') { throw 'Catalog smoke test failed.' }
}
[pscustomobject]@{ Scope = $Scope; BaseUrl = $BaseUrl; Status = 'PASS' }

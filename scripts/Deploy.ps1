[CmdletBinding(SupportsShouldProcess = $true)]
param(
    [ValidateSet('Theme', 'ProductQr', 'Pwa')]
    [string] $Component = 'Theme',
    [string] $ConfigPath = (Join-Path $PSScriptRoot '..\config\local.ps1')
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\Project.Common.ps1')
$config = Import-ProjectConfig -Path $ConfigPath
$spec = if ($Component -eq 'Theme') {
    @{ Local = Join-Path $PSScriptRoot '..\theme\blocksy-child'; Slug = 'blocksy-child'; Relative = 'wp-content/themes'; Activate = 'theme' }
} elseif ($Component -eq 'ProductQr') {
    @{ Local = Join-Path $PSScriptRoot '..\plugin\tt-product-qr'; Slug = 'tt-product-qr'; Relative = 'wp-content/plugins'; Activate = 'plugin' }
} else {
    @{ Local = Join-Path $PSScriptRoot '..\plugin\tt-pwa'; Slug = 'tt-pwa'; Relative = 'wp-content/plugins'; Activate = 'plugin' }
}
$source = [IO.Path]::GetFullPath($spec.Local)
if (-not (Test-Path -LiteralPath $source -PathType Container)) { throw "Component source is missing: $Component" }
if (Get-ChildItem -LiteralPath $source -Recurse -Force | Where-Object { $_.Attributes -band [IO.FileAttributes]::ReparsePoint }) { throw 'Component source contains a symlink or reparse point.' }

$id = [guid]::NewGuid().ToString('N')
$archive = Join-Path ([IO.Path]::GetTempPath()) "tt-deploy-$id.tar.gz"
$remoteArchive = "/tmp/tt-deploy-$id.tar.gz"
try {
    & tar -czf $archive -C (Split-Path $source -Parent) (Split-Path $source -Leaf)
    if ($LASTEXITCODE -ne 0) { throw 'Could not package component.' }
    if (-not $PSCmdlet.ShouldProcess("$($config.ProjectServerHost):$($config.ProjectWebRoot)/$($spec.Relative)/$($spec.Slug)", "Deploy $Component")) { return }
    Copy-ProjectScp -Config $config -SourcePath $archive -DestinationPath $remoteArchive | Out-Null
    $target = "$($config.ProjectWebRoot)/$($spec.Relative)/$($spec.Slug)"
    $parent = $target.Substring(0, $target.LastIndexOf('/'))
    $remote = @"
set -Eeuo pipefail
archive='$remoteArchive'
stage='/tmp/tt-stage-$id'
target='$target'
rollback="/www/backup/site/thuc-pham-thuy-trang/deploy/`$(date -u +%Y%m%dT%H%M%SZ)-$($spec.Slug).tar.gz"
trap 'rm -f -- "`$archive"; rm -rf -- "`$stage"' EXIT
mkdir -p -- "`$stage" /www/backup/site/thuc-pham-thuy-trang/deploy
chmod 700 /www/backup/site/thuc-pham-thuy-trang/deploy
tar -xzf "`$archive" -C "`$stage"
test -f "`$stage/$($spec.Slug)/$(if ($Component -eq 'Theme') { 'style.css' } elseif ($Component -eq 'ProductQr') { 'tt-product-qr.php' } else { 'tt-pwa.php' })"
if find "`$stage/$($spec.Slug)" -type l | grep -q .; then printf 'Symlink rejected.\n' >&2; exit 8; fi
if [ -d "`$target" ]; then tar -czf "`$rollback" -C '$parent' '$($spec.Slug)'; chmod 600 "`$rollback"; fi
install -d -m 755 -o root -g www "`$target"
cp -a "`$stage/$($spec.Slug)/." "`$target/"
chown -R root:www "`$target"
find "`$target" -type d -exec chmod 755 {} +
find "`$target" -type f -exec chmod 644 {} +
find "`$target" -type f -name '*.php' -exec php -l {} \; | grep -v 'No syntax errors detected' && exit 9 || true
wp $($spec.Activate) activate '$($spec.Slug)' --path='$($config.ProjectWebRoot)' --allow-root
"@
    Invoke-ProjectSsh -Config $config -Command $remote | Out-Null
}
finally {
    if (Test-Path -LiteralPath $archive -PathType Leaf) { Remove-Item -LiteralPath $archive -Force }
}

[pscustomobject]@{ Component = $Component; Target = "$($config.ProjectWebRoot)/$($spec.Relative)/$($spec.Slug)"; Status = 'DEPLOYED' }

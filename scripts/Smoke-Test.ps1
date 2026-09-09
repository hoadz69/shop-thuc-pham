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

function Convert-ResponseContentToText {
    param([Parameter(Mandatory)] $Content)
    if ($Content -is [byte[]]) { return [Text.Encoding]::UTF8.GetString($Content) }
    return [string] $Content
}

$root = $config.ProjectWebRoot
if ($Scope -in @('Theme', 'All', 'Catalog')) {
    $state = @(Invoke-ProjectSsh -Config $config -Command "printf 'template='; wp option get template --path='$root' --allow-root; printf 'stylesheet='; wp option get stylesheet --path='$root' --allow-root; printf 'products='; wp post list --post_type=product --post_status=publish --format=count --path='$root' --allow-root")
    $joined = $state -join "`n"
    if ($joined -notmatch 'template=blocksy' -or $joined -notmatch 'stylesheet=blocksy-child') { throw 'Theme smoke test failed.' }
    if ($Scope -ne 'Theme' -and $joined -notmatch 'products=12') { throw 'Catalog smoke test failed.' }
}

if ($Scope -eq 'All') {
    $pluginState = @(Invoke-ProjectSsh -Config $config -Command "wp plugin is-active tt-product-qr --path='$root' --allow-root && printf 'qr=active\n'; wp plugin is-active tt-pwa --path='$root' --allow-root && printf 'pwa=active\n'; wp post list --post_type=product --post_status=publish --fields=ID,url --format=json --path='$root' --allow-root") -join "`n"
    if ($pluginState -notmatch 'qr=active' -or $pluginState -notmatch 'pwa=active') { throw 'Project plugin smoke test failed.' }
    $jsonStart = $pluginState.IndexOf('[')
    if ($jsonStart -lt 0) { throw 'Product URL smoke data is missing.' }
    $products = $pluginState.Substring($jsonStart) | ConvertFrom-Json
    $sample = $products | Select-Object -First 1
    $productHtml = (Invoke-WebRequest -Uri $sample.url -MaximumRedirection 5 -TimeoutSec 30).Content
    if ($productHtml -notlike "*data-qr-payload=`"$([Net.WebUtility]::HtmlEncode($sample.url))`"*") { throw 'QR payload smoke test failed.' }

    $manifestResponse = Invoke-WebRequest -Uri ($BaseUrl.TrimEnd('/') + '/?tt-manifest=1') -TimeoutSec 30
    $manifest = (Convert-ResponseContentToText $manifestResponse.Content) | ConvertFrom-Json
    if ($manifest.name -ne 'Thực phẩm Thủy Trang' -or $manifest.display -ne 'standalone' -or @($manifest.icons.sizes) -notcontains '192x192' -or @($manifest.icons.sizes) -notcontains '512x512') { throw 'PWA manifest smoke test failed.' }
    $workerResponse = Invoke-WebRequest -Uri ($BaseUrl.TrimEnd('/') + '/?tt-sw=1') -TimeoutSec 30
    $worker = Convert-ResponseContentToText $workerResponse.Content
    if ($workerResponse.Headers['Service-Worker-Allowed'] -ne '/' -or $worker -notmatch 'wp-admin' -or $worker -notmatch 'checkout') { throw 'PWA service worker smoke test failed.' }

    $fatalState = @(Invoke-ProjectSsh -Config $config -Command "if find '$root/wp-content' -maxdepth 2 -type f \( -name debug.log -o -name error_log \) -mmin -10 -exec tail -n 100 {} \; 2>/dev/null | grep -Eiq 'PHP (Fatal error|Parse error)'; then printf 'fatal=found\n'; else printf 'fatal=none\n'; fi") -join "`n"
    if ($fatalState -notmatch 'fatal=none') { throw 'A recent PHP fatal or parse error was found.' }
}
[pscustomobject]@{ Scope = $Scope; BaseUrl = $BaseUrl; Status = 'PASS' }

[CmdletBinding()]
param(
    [string] $ConfigPath = (Join-Path $PSScriptRoot '..\config\local.ps1'),
    [switch] $UpdateDoc
)

$ErrorActionPreference = 'Stop'
$commonPath = Join-Path $PSScriptRoot 'lib\Project.Common.ps1'
if (-not (Get-Command Import-ProjectConfig -ErrorAction SilentlyContinue)) {
    . $commonPath
}

$config = Import-ProjectConfig -Path $ConfigPath
$webRoot = $config.ProjectWebRoot
$systemCommand = @"
set -eu
printf 'disk='; df -P '$webRoot' | awk 'NR==2 {printf "%s/%s/%s", `$3, `$2, `$4}'
printf '\nstat='; stat -c '%U:%G:%a' '$webRoot'
printf '\nsource_bytes='; du -sb '$webRoot' | awk '{print `$1}'
printf '\n'
"@
$systemLines = @(Invoke-ProjectSsh -Config $config -Command $systemCommand)
$system = [ordered]@{}
foreach ($line in $systemLines) {
    if ($line -match '^([^=]+)=(.*)$') { $system[$Matches[1]] = $Matches[2] }
}

$phpSource = @'
$root = '__WEB_ROOT__';
require $root . '/wp-includes/version.php';
require $root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$themes = array_keys(wp_get_themes());
$plugins = array();
foreach (get_plugins() as $file => $data) {
    $plugins[] = array('name' => dirname($file) === '.' ? basename($file, '.php') : dirname($file), 'active' => is_plugin_active($file));
}
$count_posts = static function ($type) {
    $counts = wp_count_posts($type);
    return isset($counts->publish) ? (int) $counts->publish : 0;
};
$uploadFiles = 0;
$upload = wp_upload_dir();
if (empty($upload['error']) && is_dir($upload['basedir'])) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload['basedir'], FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $entry) { if ($entry->isFile()) { $uploadFiles++; } }
}
echo wp_json_encode(array(
    'wordpressVersion' => $wp_version,
    'siteUrl' => get_option('siteurl'),
    'homeUrl' => get_option('home'),
    'activeTheme' => get_stylesheet(),
    'themes' => $themes,
    'plugins' => $plugins,
    'counts' => array('products' => $count_posts('product'), 'orders' => $count_posts('shop_order'), 'pages' => $count_posts('page')),
    'uploadFiles' => $uploadFiles
), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
'@
$phpSource = $phpSource.Replace('__WEB_ROOT__', $webRoot.Replace("'", "\\'"))
$encoded = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($phpSource))
$inventoryMarker = '# read-only inventory: wp-includes/version.php wp_get_themes get_plugins product shop_order page uploads'
$phpCommand = "php -r 'eval(base64_decode(`"$encoded`"));'"
$jsonLine = (Invoke-ProjectSsh -Config $config -Command "$inventoryMarker`n$phpCommand") -join ''
$wordpress = $jsonLine | ConvertFrom-Json

$inventory = [ordered]@{
    collectedAtUtc = [DateTime]::UtcNow.ToString('yyyy-MM-ddTHH:mm:ssZ')
    webRoot = $webRoot
    disk = $system.disk
    rootStat = $system.stat
    sourceBytes = if ($system.source_bytes) { [int64] $system.source_bytes } else { $null }
    wordpressVersion = $wordpress.wordpressVersion
    siteUrl = $wordpress.siteUrl
    homeUrl = $wordpress.homeUrl
    activeTheme = $wordpress.activeTheme
    themes = @($wordpress.themes)
    plugins = @($wordpress.plugins)
    counts = $wordpress.counts
    uploadFiles = [int] $wordpress.uploadFiles
}
$json = $inventory | ConvertTo-Json -Depth 8

if ($UpdateDoc) {
    $docPath = Join-Path $PSScriptRoot '..\docs\server-inventory.md'
    $doc = Get-Content -Raw -LiteralPath $docPath
    $start = '<!-- AUTO-INVENTORY:START -->'
    $end = '<!-- AUTO-INVENTORY:END -->'
    $fence = '```'
    $block = "$start`n## Khảo sát tự động gần nhất`n`n${fence}json`n$json`n$fence`n$end"
    if ($doc.Contains($start) -and $doc.Contains($end)) {
        $pattern = '(?s)' + [regex]::Escape($start) + '.*?' + [regex]::Escape($end)
        $doc = [regex]::Replace($doc, $pattern, $block)
    }
    else {
        $doc = $doc.TrimEnd() + "`n`n$block"
    }
    Set-Content -LiteralPath $docPath -Value $doc -Encoding UTF8
}

$json

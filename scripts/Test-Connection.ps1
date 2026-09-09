[CmdletBinding()]
param(
    [string] $ConfigPath = (Join-Path $PSScriptRoot '..\config\local.ps1')
)

$ErrorActionPreference = 'Stop'
$commonPath = Join-Path $PSScriptRoot 'lib\Project.Common.ps1'
if (-not (Get-Command Import-ProjectConfig -ErrorAction SilentlyContinue)) {
    . $commonPath
}

$config = Import-ProjectConfig -Path $ConfigPath
$webRoot = $config.ProjectWebRoot
$command = @"
set -eu
printf 'hostname='; hostname
printf 'uid='; id -u
printf 'disk_available_kb='; df -P '$webRoot' | awk 'NR==2 {print `$4}'
if [ -d '$webRoot' ]; then printf 'web_root_exists=true\n'; else printf 'web_root_exists=false\n'; fi
printf 'owner='; stat -c '%U' '$webRoot'
printf 'group='; stat -c '%G' '$webRoot'
for tool in php mysql mysqldump tar gzip sha256sum wp; do
  printf '%s=' "`$tool"
  command -v "`$tool" 2>/dev/null || printf 'missing'
  printf '\n'
done
"@

$lines = @(Invoke-ProjectSsh -Config $config -Command $command)
$result = [ordered]@{}
foreach ($line in $lines) {
    if ($line -match '^([^=]+)=(.*)$') {
        $result[$Matches[1]] = $Matches[2]
    }
}

foreach ($required in @('hostname', 'uid', 'disk_available_kb', 'web_root_exists', 'owner', 'group')) {
    if (-not $result.Contains($required)) {
        throw "Preflight response is missing '$required'."
    }
}
if ($result.web_root_exists -ne 'true') { throw 'Configured web root does not exist.' }
if ([int64] $result.disk_available_kb -lt 1048576) { throw 'Less than 1 GiB is available on the web-root filesystem.' }

[pscustomobject] $result

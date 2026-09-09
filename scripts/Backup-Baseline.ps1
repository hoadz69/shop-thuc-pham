[CmdletBinding()]
param(
    [string] $ConfigPath = (Join-Path $PSScriptRoot '..\config\local.ps1'),
    [ValidatePattern('^[A-Za-z0-9][A-Za-z0-9._-]*$')]
    [string] $Label = 'baseline'
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\Project.Common.ps1')
$config = Import-ProjectConfig -Path $ConfigPath

# The remote script performs its disk check before mysqldump and deletes only its exact uploaded path.
$remoteScript = "/tmp/tt-backup-$([guid]::NewGuid().ToString('N')).sh"
$localScript = Join-Path $PSScriptRoot 'remote\backup-baseline.sh'
Copy-ProjectScp -Config $config -SourcePath $localScript -DestinationPath $remoteScript | Out-Null
$quotedRoot = "'$($config.ProjectWebRoot)'"
$remoteCommand = "chmod 700 '$remoteScript'; if ! bash -n '$remoteScript'; then rm -f -- '$remoteScript'; exit 5; fi; '$remoteScript' $quotedRoot"
$output = @(Invoke-ProjectSsh -Config $config -Command $remoteCommand)
$remotePath = $output | Where-Object { $_ -match '^/www/backup/site/thuc-pham-thuy-trang/[0-9]{8}T[0-9]{6}Z$' } | Select-Object -Last 1
if (-not $remotePath) { throw 'Remote backup did not return a valid artifact path.' }

$stamp = Split-Path -Leaf $remotePath
$backupRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\backups'))
$localPath = Join-Path $backupRoot "$stamp-$Label"
New-Item -ItemType Directory -Path $localPath -Force | Out-Null
foreach ($name in @('database.sql.gz', 'uploads.tar.gz', 'site-source.tar.gz', 'environment.txt', 'SHA256SUMS')) {
    Copy-ProjectScp -Config $config -SourcePath "$remotePath/$name" -DestinationPath (Join-Path $localPath $name) -FromRemote | Out-Null
}
$verifierPath = Join-Path $PSScriptRoot 'Verify-Backup.ps1'
$verified = & $verifierPath -BackupPath $localPath
if ($verified.Status -ne 'PASS') { throw 'Local backup verification did not pass.' }

$size = (Get-ChildItem -LiteralPath $localPath -File | Measure-Object Length -Sum).Sum
[pscustomobject]@{ Timestamp = $stamp; RemotePath = $remotePath; LocalPath = $localPath; Bytes = $size; Verification = 'PASS' }

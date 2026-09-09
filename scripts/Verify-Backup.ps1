[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string] $BackupPath,

    [string] $BackupRoot = (Join-Path $PSScriptRoot '..\backups')
)

$ErrorActionPreference = 'Stop'
$root = [IO.Path]::GetFullPath($BackupRoot).TrimEnd([IO.Path]::DirectorySeparatorChar, [IO.Path]::AltDirectorySeparatorChar)
$path = [IO.Path]::GetFullPath($BackupPath).TrimEnd([IO.Path]::DirectorySeparatorChar, [IO.Path]::AltDirectorySeparatorChar)
$comparison = if ($IsWindows) { [StringComparison]::OrdinalIgnoreCase } else { [StringComparison]::Ordinal }
if (-not $path.StartsWith($root + [IO.Path]::DirectorySeparatorChar, $comparison)) {
    throw 'BackupPath must be a child of the configured backups directory.'
}
if (-not (Test-Path -LiteralPath $path -PathType Container)) { throw 'Backup directory does not exist.' }

$required = @('database.sql.gz', 'uploads.tar.gz', 'site-source.tar.gz', 'environment.txt')
$checksumPath = Join-Path $path 'SHA256SUMS'
if (-not (Test-Path -LiteralPath $checksumPath -PathType Leaf)) { throw 'SHA256SUMS is missing.' }
$seen = [System.Collections.Generic.HashSet[string]]::new([StringComparer]::Ordinal)
foreach ($line in Get-Content -LiteralPath $checksumPath) {
    if ($line -notmatch '^([a-fA-F0-9]{64})  ([A-Za-z0-9][A-Za-z0-9._-]*)$') { throw 'Malformed checksum line.' }
    $expected, $name = $Matches[1].ToLowerInvariant(), $Matches[2]
    if (-not $seen.Add($name)) { throw "Duplicate checksum entry: $name" }
    $filePath = Join-Path $path $name
    if (-not (Test-Path -LiteralPath $filePath -PathType Leaf)) { throw "Backup file is missing: $name" }
    $actual = (Get-FileHash -LiteralPath $filePath -Algorithm SHA256).Hash.ToLowerInvariant()
    if ($actual -ne $expected) { throw "Checksum mismatch: $name" }
}
foreach ($name in $required) { if (-not $seen.Contains($name)) { throw "Checksum entry is missing: $name" } }

$gzip = Get-Command gzip -ErrorAction SilentlyContinue
if (-not $gzip -and $IsWindows) {
    $fallback = 'C:\Program Files\Git\usr\bin\gzip.exe'
    if (Test-Path -LiteralPath $fallback -PathType Leaf) { $gzip = Get-Item -LiteralPath $fallback }
}
if (-not $gzip) { throw 'gzip executable is required to verify the SQL stream.' }
$gzipPath = if ($gzip.Source) { $gzip.Source } else { $gzip.FullName }
& $gzipPath -t (Join-Path $path 'database.sql.gz')
if ($LASTEXITCODE -ne 0) { throw 'database.sql.gz failed gzip integrity validation.' }

foreach ($archive in @('uploads.tar.gz', 'site-source.tar.gz')) {
    $entries = @(& tar -tzf (Join-Path $path $archive))
    if ($LASTEXITCODE -ne 0) { throw "$archive failed tar validation." }
    if ($entries.Count -eq 0) { throw "$archive is empty." }
    foreach ($entry in $entries) {
        if ($entry -match '^(?:/|\\)' -or $entry -match '(^|[\\/])\.\.([\\/]|$)') { throw "$archive contains an unsafe path." }
    }
    if ($archive -eq 'uploads.tar.gz' -and -not ($entries | Where-Object { $_ -notmatch '^(?:\./)?uploads/?$' -and $_ -notmatch '/$' })) { throw 'uploads.tar.gz contains no file entry.' }
}

$stream = [IO.File]::OpenRead((Join-Path $path 'database.sql.gz'))
try {
    $gz = [IO.Compression.GZipStream]::new($stream, [IO.Compression.CompressionMode]::Decompress)
    $reader = [IO.StreamReader]::new($gz)
    try {
        $hasCreate = $false; $hasInsert = $false
        $hasOptions = $false; $hasPosts = $false; $hasPostmeta = $false
        while (($line = $reader.ReadLine()) -ne $null) {
            if ($line -match '^CREATE TABLE ') { $hasCreate = $true }
            if ($line -match '^INSERT INTO ') { $hasInsert = $true }
            if ($line -match '^CREATE TABLE `[^`]+_options`') { $hasOptions = $true }
            if ($line -match '^CREATE TABLE `[^`]+_posts`') { $hasPosts = $true }
            if ($line -match '^CREATE TABLE `[^`]+_postmeta`') { $hasPostmeta = $true }
        }
    }
    finally { $reader.Dispose(); $gz.Dispose() }
}
finally { $stream.Dispose() }
if (-not ($hasCreate -and $hasInsert -and $hasOptions -and $hasPosts -and $hasPostmeta)) {
    throw 'SQL dump does not contain the required WordPress table structure and data markers.'
}

[pscustomobject]@{ Path = $path; Files = $seen.Count; Status = 'PASS' }

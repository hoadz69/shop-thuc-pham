Describe 'Backup verifier' {
    BeforeAll {
        $script:verifierPath = Join-Path $PSScriptRoot '..\..\scripts\Verify-Backup.ps1'
        $script:source = Get-Content -Raw -LiteralPath $script:verifierPath

        function New-BackupFixture {
            param([string] $Root, [string] $Sql, [switch] $EmptyUploads)
            $id = [guid]::NewGuid().ToString('N')
            $path = Join-Path $Root "fixture-$id"
            New-Item -ItemType Directory -Path $path -Force | Out-Null
            $sqlPath = Join-Path $path 'database.sql.gz'
            $file = [IO.File]::Create($sqlPath)
            $gzip = [IO.Compression.GZipStream]::new($file, [IO.Compression.CompressionMode]::Compress)
            $writer = [IO.StreamWriter]::new($gzip)
            $writer.Write($Sql); $writer.Dispose(); $gzip.Dispose(); $file.Dispose()

            $content = Join-Path $Root "content-$id"
            New-Item -ItemType Directory -Path (Join-Path $content 'uploads') -Force | Out-Null
            if (-not $EmptyUploads) { Set-Content -LiteralPath (Join-Path $content 'uploads\image.txt') -Value 'fixture' }
            Set-Content -LiteralPath (Join-Path $content 'source.txt') -Value 'fixture'
            & tar -czf (Join-Path $path 'uploads.tar.gz') -C $content uploads
            & tar -czf (Join-Path $path 'site-source.tar.gz') -C $content source.txt
            Set-Content -LiteralPath (Join-Path $path 'environment.txt') -Value 'fixture=true'
            $lines = foreach ($name in @('database.sql.gz','uploads.tar.gz','site-source.tar.gz','environment.txt')) {
                $hash = (Get-FileHash -LiteralPath (Join-Path $path $name) -Algorithm SHA256).Hash.ToLowerInvariant()
                "$hash  $name"
            }
            Set-Content -LiteralPath (Join-Path $path 'SHA256SUMS') -Value $lines
            $path
        }
    }

    It 'validates checksums before archive and SQL inspection' {
        $script:source.IndexOf('Get-FileHash') | Should -BeLessThan $script:source.IndexOf('gzip -ErrorAction')
        $script:source | Should -Match 'Checksum mismatch'
        $script:source | Should -Match 'Malformed checksum line'
    }

    It 'rejects traversal and requires non-empty uploads' {
        $script:source | Should -Match 'unsafe path'
        $script:source | Should -Match 'contains no file entry'
    }

    It 'requires generic-prefix WordPress core tables and SQL data' {
        $script:source | Should -Match '\[\^`\]\+_options'
        $script:source | Should -Match '\[\^`\]\+_posts'
        $script:source | Should -Match '\[\^`\]\+_postmeta'
        $script:source | Should -Match 'INSERT INTO'
    }

    It 'fails on a checksum mismatch before accepting the fixture' {
        $sql = "CREATE TABLE ``x_options`` (id int);`nCREATE TABLE ``x_posts`` (id int);`nCREATE TABLE ``x_postmeta`` (id int);`nINSERT INTO ``x_options`` VALUES (1);"
        $path = New-BackupFixture -Root $TestDrive -Sql $sql
        Add-Content -LiteralPath (Join-Path $path 'environment.txt') -Value 'tampered=true'
        { & $script:verifierPath -BackupPath $path -BackupRoot $TestDrive } | Should -Throw '*Checksum mismatch*'
    }

    It 'fails when generic SQL lacks the WordPress table structure' {
        $path = New-BackupFixture -Root $TestDrive -Sql 'CREATE TABLE `other` (id int); INSERT INTO `other` VALUES (1);'
        { & $script:verifierPath -BackupPath $path -BackupRoot $TestDrive } | Should -Throw '*WordPress table structure*'
    }

    It 'fails when the uploads archive has no file entry' {
        $sql = "CREATE TABLE ``x_options`` (id int);`nCREATE TABLE ``x_posts`` (id int);`nCREATE TABLE ``x_postmeta`` (id int);`nINSERT INTO ``x_options`` VALUES (1);"
        $path = New-BackupFixture -Root $TestDrive -Sql $sql -EmptyUploads
        { & $script:verifierPath -BackupPath $path -BackupRoot $TestDrive } | Should -Throw '*no file entry*'
    }
}

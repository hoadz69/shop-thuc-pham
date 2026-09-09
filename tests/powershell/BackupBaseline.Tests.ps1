Describe 'Baseline backup safety' {
    BeforeAll {
        $scriptPath = Join-Path $PSScriptRoot '..\..\scripts\remote\backup-baseline.sh'
        $orchestratorPath = Join-Path $PSScriptRoot '..\..\scripts\Backup-Baseline.ps1'
        $script:remoteSource = Get-Content -Raw -LiteralPath $scriptPath
        $script:orchestratorSource = Get-Content -Raw -LiteralPath $orchestratorPath
    }

    It 'fails closed and checks disk before mysqldump' {
        $script:remoteSource | Should -Match 'set -Eeuo pipefail'
        $script:remoteSource.IndexOf('available_kb=') | Should -BeLessThan $script:remoteSource.IndexOf('mysqldump ')
        $script:remoteSource | Should -Match '--single-transaction --quick --skip-lock-tables'
    }

    It 'places artifacts in scoped server and ignored local directories' {
        $script:remoteSource | Should -Match '/www/backup/site/thuc-pham-thuy-trang'
        $script:orchestratorSource | Should -Match "\.\.\\backups"
        (Get-Content -Raw (Join-Path $PSScriptRoot '..\..\.gitignore')) | Should -Match '(?m)^backups/$'
    }

    It 'downloads every required artifact and invokes verification' {
        foreach ($name in @('database.sql.gz','uploads.tar.gz','site-source.tar.gz','environment.txt','SHA256SUMS')) {
            $script:orchestratorSource | Should -Match ([regex]::Escape($name))
        }
        $script:orchestratorSource | Should -Match 'Verify-Backup\.ps1'
    }
}

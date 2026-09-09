Describe 'TT PWA source' {
    BeforeAll {
        $root = Join-Path $PSScriptRoot '..\..'
        $php = Get-Content -Raw (Join-Path $root 'plugin\tt-pwa\src\Pwa.php')
        $register = Get-Content -Raw (Join-Path $root 'plugin\tt-pwa\assets\register.js')
    }
    It 'provides required manifest fields and same-origin assets' {
        foreach ($field in 'name','short_name','start_url','display','theme_color','background_color','192x192','512x512') { $php | Should -Match ([regex]::Escape($field)) }
        $php | Should -Match 'plugin_dir_url'
    }
    It 'never registers a service worker on an insecure remote origin' {
        $register | Should -Match "location\.protocol === 'https:'"
        $register | Should -Match 'waiting-https'
    }
    It 'bypasses sensitive and mutable WooCommerce requests' {
        foreach ($term in 'wp-admin','wp-login','cart','checkout','tai-khoan','my-account','_wpnonce','wc-ajax','add-to-cart') { $php | Should -Match ([regex]::Escape($term)) }
        $php | Should -Match "request\.method !== 'GET'"
    }
}

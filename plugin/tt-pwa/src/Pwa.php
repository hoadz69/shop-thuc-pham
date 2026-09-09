<?php
defined( 'ABSPATH' ) || exit;

final class TT_PWA {
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'endpoint' ), 0 );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_worker' ) );
	}

	public static function manifest_url() {
		return add_query_arg( 'tt-manifest', '1', home_url( '/' ) );
	}

	public static function worker_url() {
		return add_query_arg( 'tt-sw', '1', home_url( '/' ) );
	}

	public static function head() {
		printf( '<link rel="manifest" href="%s">' . "\n", esc_url( self::manifest_url() ) );
		echo '<meta name="theme-color" content="#187642">' . "\n";
		echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	}

	public static function register_worker() {
		wp_enqueue_script( 'tt-pwa-register', plugin_dir_url( TT_PWA_FILE ) . 'assets/register.js', array(), TT_PWA_VERSION, true );
		wp_localize_script(
			'tt-pwa-register',
			'TTPwa',
			array(
				'workerUrl' => self::worker_url(),
				'scope'     => '/',
			)
		);
	}

	public static function endpoint() {
		if ( isset( $_GET['tt-manifest'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['tt-manifest'] ) ) ) {
			self::manifest();
		}
		if ( isset( $_GET['tt-sw'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['tt-sw'] ) ) ) {
			self::service_worker();
		}
	}

	private static function manifest() {
		$base = plugin_dir_url( TT_PWA_FILE );
		$manifest = array(
			'name'             => 'Thực phẩm Thủy Trang',
			'short_name'       => 'Thủy Trang',
			'description'      => 'Cửa hàng thực phẩm tươi và tiện lợi.',
			'lang'             => 'vi',
			'start_url'        => home_url( '/?source=pwa' ),
			'scope'            => home_url( '/' ),
			'display'          => 'standalone',
			'theme_color'      => '#187642',
			'background_color' => '#f7fbf8',
			'icons'            => array(
				array( 'src' => $base . 'assets/icon-192.svg', 'sizes' => '192x192', 'type' => 'image/svg+xml', 'purpose' => 'any maskable' ),
				array( 'src' => $base . 'assets/icon-512.svg', 'sizes' => '512x512', 'type' => 'image/svg+xml', 'purpose' => 'any maskable' ),
			),
		);
		nocache_headers();
		header( 'Content-Type: application/manifest+json; charset=utf-8' );
		echo wp_json_encode( $manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		exit;
	}

	private static function service_worker() {
		$home = wp_json_encode( home_url( '/' ) );
		$shop = wp_json_encode( home_url( '/products/' ) );
		nocache_headers();
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Service-Worker-Allowed: /' );
		echo "const CACHE='tt-storefront-v1';\n";
		echo "const SHELL=[$home,$shop];\n";
		?>
self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key.startsWith('tt-storefront-') && key !== CACHE).map(key => caches.delete(key)))).then(() => self.clients.claim()));
});
function bypass(request, url) {
  if (request.method !== 'GET' || url.origin !== self.location.origin) return true;
  if (/\/(wp-admin|wp-login\.php|cart|checkout|tai-khoan|my-account)(\/|$)/i.test(url.pathname)) return true;
  return ['_wpnonce', 'wc-ajax', 'add-to-cart'].some(key => url.searchParams.has(key));
}
self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);
  if (bypass(request, url)) return;
  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).then(response => {
      if (response.ok) caches.open(CACHE).then(cache => cache.put(request, response.clone()));
      return response;
    }).catch(() => caches.match(request).then(hit => hit || caches.match(SHELL[0]))));
    return;
  }
  event.respondWith(caches.match(request).then(hit => hit || fetch(request).then(response => {
    if (response.ok && ['style', 'script', 'image', 'font'].includes(request.destination)) caches.open(CACHE).then(cache => cache.put(request, response.clone()));
    return response;
  })));
});
		<?php
		exit;
	}
}

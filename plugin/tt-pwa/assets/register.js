(function () {
  var secure = window.location.protocol === 'https:' || ['localhost', '127.0.0.1'].includes(window.location.hostname);
  document.documentElement.dataset.pwaStatus = secure ? 'ready' : 'waiting-https';
  if (!secure || !('serviceWorker' in navigator) || typeof TTPwa === 'undefined') return;
  window.addEventListener('load', function () {
    navigator.serviceWorker.register(TTPwa.workerUrl, { scope: TTPwa.scope });
  });
}());

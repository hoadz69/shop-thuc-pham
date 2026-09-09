(function () {
  function render(element) {
    var payload = element.getAttribute('data-qr-payload');
    var size = parseInt(element.getAttribute('data-qr-size') || '180', 10);
    if (!payload || !/^https?:\/\//i.test(payload) || typeof QRCode === 'undefined') return;
    element.textContent = '';
    new QRCode(element, { text: payload, width: size, height: size, colorDark: '#10281e', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
  }
  document.querySelectorAll('.tt-product-qr-code').forEach(render);
}());

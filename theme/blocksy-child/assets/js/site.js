(function () {
  document.documentElement.classList.add('tt-js');
  document.querySelectorAll('.tt-category').forEach(function (card) {
    card.addEventListener('pointermove', function (event) {
      var rect = card.getBoundingClientRect();
      card.style.setProperty('--x', ((event.clientX - rect.left) / rect.width * 100) + '%');
    });
  });
}());

(function () {
  document.documentElement.classList.add('tt-js');
  document.querySelectorAll('.tt-category').forEach(function (card) {
    card.addEventListener('pointermove', function (event) {
      var rect = card.getBoundingClientRect();
      card.style.setProperty('--x', ((event.clientX - rect.left) / rect.width * 100) + '%');
    });
  });

  var toggle = document.querySelector('.tt-menu-toggle');
  var menu = document.querySelector('.tt-main-nav');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      var open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
      menu.classList.toggle('is-open', !open);
    });
    menu.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        toggle.setAttribute('aria-expanded', 'false');
        menu.classList.remove('is-open');
      }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        toggle.setAttribute('aria-expanded', 'false');
        menu.classList.remove('is-open');
      }
    });
  }
}());

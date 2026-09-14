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

  document.querySelectorAll('[data-tt-slider]').forEach(function (slider) {
    var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-tt-slide]'));
    var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-tt-slide-to]'));
    var previous = slider.querySelector('[data-tt-slider-prev]');
    var next = slider.querySelector('[data-tt-slider-next]');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var current = 0;
    var timer = null;

    if (slides.length < 2) return;

    function show(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach(function (slide, slideIndex) {
        var active = slideIndex === current;
        slide.classList.toggle('is-active', active);
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        slide.inert = !active;
      });
      dots.forEach(function (dot, dotIndex) {
        var active = dotIndex === current;
        dot.classList.toggle('is-active', active);
        dot.setAttribute('aria-current', active ? 'true' : 'false');
        dot.tabIndex = active ? 0 : -1;
      });
    }

    function stop() {
      if (timer) window.clearInterval(timer);
      timer = null;
    }

    function start() {
      stop();
      if (!reduceMotion && !document.hidden) {
        timer = window.setInterval(function () { show(current + 1); }, 6500);
      }
    }

    function select(index) {
      show(index);
      start();
    }

    if (previous) previous.addEventListener('click', function () { select(current - 1); });
    if (next) next.addEventListener('click', function () { select(current + 1); });
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () { select(Number(dot.getAttribute('data-tt-slide-to'))); });
    });
    slider.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') select(current - 1);
      if (event.key === 'ArrowRight') select(current + 1);
    });
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', function (event) {
      if (!slider.contains(event.relatedTarget)) start();
    });
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) stop(); else start();
    });

    show(0);
    start();
  });
}());

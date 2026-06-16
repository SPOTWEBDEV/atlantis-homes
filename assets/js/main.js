/**
 * main.js — behaviour shared by every page on the site:
 *   1. Mobile menu open/close
 *   2. Scroll-triggered fade/rise-in for any element with [data-reveal]
 *   3. Animated number counters for any element with [data-count-to]
 */

// ---------------------------------------------------------------------
// 1. Mobile menu
// ---------------------------------------------------------------------
(function initMobileMenu() {
  const btn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  const iconOpen = document.getElementById('menu-icon-open');
  const iconClose = document.getElementById('menu-icon-close');
  if (!btn || !menu) return;

  btn.addEventListener('click', () => {
    const isOpen = !menu.classList.contains('hidden');
    menu.classList.toggle('hidden');
    iconOpen.classList.toggle('hidden');
    iconClose.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', String(!isOpen));
  });
})();

// ---------------------------------------------------------------------
// 2. Scroll-triggered reveal
// ---------------------------------------------------------------------
(function initScrollReveal() {
  const targets = document.querySelectorAll('[data-reveal]');
  if (!targets.length) return;

  if (!('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('is-visible'));
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          // Optional stagger via data-reveal-delay="120" (milliseconds)
          const delay = Number(entry.target.dataset.revealDelay || 0);
          setTimeout(() => entry.target.classList.add('is-visible'), delay);
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
  );

  targets.forEach((el) => observer.observe(el));
})();

// ---------------------------------------------------------------------
// 3. Animated stat counters — counts up from 0 to data-count-to once visible
// ---------------------------------------------------------------------
(function initCounters() {
  const counters = document.querySelectorAll('[data-count-to]');
  if (!counters.length) return;

  function animateCounter(el) {
    const target = parseFloat(el.dataset.countTo);
    const suffix = el.dataset.countSuffix || '';
    const duration = 1400;
    const start = performance.now();

    function tick(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
      const value = target * eased;
      el.textContent = (Number.isInteger(target) ? Math.round(value) : value.toFixed(1)) + suffix;
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  if (!('IntersectionObserver' in window)) {
    counters.forEach(animateCounter);
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.4 }
  );
  counters.forEach((el) => observer.observe(el));
})();

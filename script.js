/* ============================================================
   SELLER CIRCLE FBA — Centralized Script
   Version: 1.0.0
   ============================================================ */

/* ── CENTRALIZED LINKS SYSTEM ── */
const LINKS = {
  checkout:  'https://ana-tagrba.systeme.io/68684523',
  register:  'https://ana-tagrba.systeme.io/68684523',
  login:     'pages/login.html',
  dashboard: 'pages/dashboard.html',
  course:    'pages/course.html',
  thankyou:  'pages/thank-you.html',
  privacy:   'pages/privacy.html',
  terms:     'pages/terms.html',
  refund:    'pages/refund.html',
  whatsapp:  'https://wa.me/201030435954',
  support:   'mailto:support@sellercircle.com',
  systeme:   'https://ana-tagrba.systeme.io/68684523'
};

/* ── INJECT LINKS INTO ALL CTA BUTTONS ── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-link]').forEach(el => {
    const key = el.dataset.link;
    if (LINKS[key]) {
      if (el.tagName === 'A') el.href = LINKS[key];
      else el.onclick = () => window.location.href = LINKS[key];
    }
  });

  initAnimations();
  initCounter();
  initNavbar();
  initMobileMenu();
  initFAQ();
  initSmoothScroll();
  initResultsGallery();
});

/* ── STICKY NAVBAR ── */
function initNavbar() {
  const nav = document.querySelector('nav, .navbar');
  if (!nav) return;
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 50);
  });
}

/* ── MOBILE HAMBURGER MENU ── */
function initMobileMenu() {
  const toggle = document.getElementById('menuToggle');
  const menu   = document.getElementById('mobileMenu');
  if (!toggle || !menu) return;
  toggle.addEventListener('click', () => {
    menu.classList.toggle('open');
    toggle.classList.toggle('active');
  });
  document.querySelectorAll('#mobileMenu a').forEach(a => {
    a.addEventListener('click', () => {
      menu.classList.remove('open');
      toggle.classList.remove('active');
    });
  });
}

/* ── SMOOTH SCROLLING ── */
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
}

/* ── SCROLL REVEAL ANIMATIONS ── */
function initAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
}

/* ── ANIMATED COUNTERS ── */
function initCounter() {
  const counters = document.querySelectorAll('[data-count]');
  if (!counters.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el     = entry.target;
      const target = +el.dataset.count;
      const suffix = el.dataset.suffix || '';
      const dur    = 2000;
      const step   = target / (dur / 16);
      let current  = 0;

      const timer = setInterval(() => {
        current += step;
        if (current >= target) {
          el.textContent = target.toLocaleString() + suffix;
          clearInterval(timer);
        } else {
          el.textContent = Math.floor(current).toLocaleString() + suffix;
        }
      }, 16);

      observer.unobserve(el);
    });
  }, { threshold: 0.5 });

  counters.forEach(c => observer.observe(c));
}

/* ── FAQ ACCORDION ── */
function initFAQ() {
  document.querySelectorAll('.faq-item').forEach(item => {
    const q = item.querySelector('.faq-q');
    if (!q) return;
    q.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });
}

/* ── RESULTS GALLERY + LIGHTBOX ── */
function initResultsGallery() {
  const imgs = document.querySelectorAll('.result-img');
  if (!imgs.length) return;

  // Create lightbox
  const lb = document.createElement('div');
  lb.id = 'lightbox';
  lb.innerHTML = `
    <div class="lb-overlay"></div>
    <div class="lb-content">
      <button class="lb-close">✕</button>
      <button class="lb-prev">‹</button>
      <img class="lb-img" src="" alt="">
      <button class="lb-next">›</button>
    </div>`;
  document.body.appendChild(lb);

  let currentIdx = 0;
  const imgList  = Array.from(imgs);

  const show = (idx) => {
    currentIdx = (idx + imgList.length) % imgList.length;
    lb.querySelector('.lb-img').src = imgList[currentIdx].src;
    lb.classList.add('active');
  };

  imgList.forEach((img, i) => img.addEventListener('click', () => show(i)));
  lb.querySelector('.lb-overlay').addEventListener('click', () => lb.classList.remove('active'));
  lb.querySelector('.lb-close').addEventListener('click', () => lb.classList.remove('active'));
  lb.querySelector('.lb-prev').addEventListener('click', () => show(currentIdx - 1));
  lb.querySelector('.lb-next').addEventListener('click', () => show(currentIdx + 1));

  document.addEventListener('keydown', e => {
    if (!lb.classList.contains('active')) return;
    if (e.key === 'ArrowLeft')  show(currentIdx - 1);
    if (e.key === 'ArrowRight') show(currentIdx + 1);
    if (e.key === 'Escape')     lb.classList.remove('active');
  });
}

/* ── COUNTDOWN TIMER ── */
function startCountdown(targetId, hours = 24) {
  const el = document.getElementById(targetId);
  if (!el) return;

  const KEY = 'sc_timer_end';
  let end = localStorage.getItem(KEY);
  if (!end || Date.now() > +end) {
    end = Date.now() + hours * 3600 * 1000;
    localStorage.setItem(KEY, end);
  }

  setInterval(() => {
    const diff = Math.max(0, +end - Date.now());
    const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
    const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
    const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
    el.textContent = `${h}:${m}:${s}`;
  }, 1000);
}

/* ── UTILITY: Navigate ── */
const go = (key) => window.location.href = LINKS[key] || key;

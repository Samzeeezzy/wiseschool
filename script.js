// WISE website interactions
const menuButton = document.querySelector('.menu-toggle');
const nav = document.querySelector('.main-nav');

menuButton?.addEventListener('click', () => {
  const isOpen = nav.classList.toggle('open');
  menuButton.setAttribute('aria-expanded', String(isOpen));
  menuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
});

document.querySelectorAll('.main-nav a').forEach(link => {
  link.addEventListener('click', () => {
    nav.classList.remove('open');
    menuButton?.setAttribute('aria-expanded', 'false');
  });
});

// Hero slider
const slides = [...document.querySelectorAll('.hero-slide')];
const dots = [...document.querySelectorAll('.dot')];
const index = document.querySelector('.hero-index strong');
let currentSlide = 0;
let sliderTimer;

function showSlide(next) {
  if (!slides.length) return;
  currentSlide = (next + slides.length) % slides.length;
  slides.forEach((slide, i) => slide.classList.toggle('active', i === currentSlide));
  dots.forEach((dot, i) => dot.classList.toggle('active', i === currentSlide));
  if (index) index.textContent = `0${currentSlide + 1}`;
}
function restartSlider() {
  clearInterval(sliderTimer);
  sliderTimer = setInterval(() => showSlide(currentSlide + 1), 6500);
}
document.querySelector('.slide-next')?.addEventListener('click', () => { showSlide(currentSlide + 1); restartSlider(); });
document.querySelector('.slide-prev')?.addEventListener('click', () => { showSlide(currentSlide - 1); restartSlider(); });
dots.forEach((dot, i) => dot.addEventListener('click', () => { showSlide(i); restartSlider(); }));
if (slides.length > 1) restartSlider();

// Footer year
const year = document.getElementById('year');
if (year) year.textContent = new Date().getFullYear();


// WISE opening-book welcome animation
(() => {
  const intro = document.createElement('div');
  intro.className = 'wise-book-intro';
  intro.setAttribute('role', 'status');
  intro.setAttribute('aria-label', 'Opening the WISE website');
  intro.innerHTML = `
    <div class="wise-intro-brand">WISDOM INTERNATIONAL SCHOOL OF EXCELLENCE</div>
    <div class="wise-book-stage" aria-hidden="true">
      <div class="wise-book">
        <div class="wise-book-pages"></div>
        <div class="wise-book-cover">
          <span class="wise-book-emblem">W</span>
          <span class="wise-book-title">WISE</span>
          <span class="wise-book-subtitle">A WORLD OF POSSIBILITIES</span>
        </div>
      </div>
      <svg class="wise-opening-hand" viewBox="0 0 180 150" role="presentation">
        <path d="M24 116 C34 99 43 83 52 67 L68 35 C72 27 83 30 82 39 L73 68 L91 20 C95 10 107 14 104 25 L91 68 L111 29 C117 20 128 27 123 37 L105 75 L128 49 C136 42 145 51 138 61 L112 96 C105 106 103 120 105 135 L45 139 Z" fill="#d7a47d" stroke="#f0c7a8" stroke-width="3" stroke-linejoin="round"/>
        <path d="M46 119 C64 108 74 91 82 74" fill="none" stroke="#b87f5c" stroke-width="3" stroke-linecap="round"/>
        <path d="M85 70 L96 43 M104 73 L119 49" fill="none" stroke="#b87f5c" stroke-width="2" stroke-linecap="round"/>
        <path d="M26 116 L16 132" stroke="#d7a47d" stroke-width="13" stroke-linecap="round"/>
      </svg>
    </div>
    <div class="wise-intro-caption">TURNING THE PAGE TO A BRIGHTER FUTURE</div>
    <button class="wise-intro-skip" type="button">Skip intro</button>
  `;

  const style = document.createElement('style');
  style.textContent = `
    .wise-book-intro{position:fixed;inset:0;z-index:99999;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:30px;background:radial-gradient(ellipse at center,#174d83 0%,#0b2948 62%,#071a30 100%);color:#fff;overflow:hidden;opacity:1;transition:opacity .65s ease,visibility .65s ease}
    .wise-book-intro.is-leaving{opacity:0;visibility:hidden;pointer-events:none}
    .wise-intro-brand{font:600 10px/1.4 Arial,sans-serif;letter-spacing:.24em;text-align:center;color:#f2d78e;padding:0 22px}
    .wise-book-stage{position:relative;width:min(340px,76vw);height:235px;perspective:1100px;display:grid;place-items:center}
    .wise-book{position:relative;width:260px;height:180px;transform-style:preserve-3d;filter:drop-shadow(0 24px 22px rgba(0,0,0,.35))}
    .wise-book-pages{position:absolute;inset:5px 0 0 0;background:linear-gradient(90deg,#e8dfc8 0 49%,#fff8e8 50% 100%);border:2px solid #d6c59e;border-radius:4px 8px 8px 4px;box-shadow:inset 0 0 0 5px rgba(160,126,68,.12)}
    .wise-book-cover{position:absolute;inset:0 0 0 0;transform-origin:left center;transform:rotateY(0deg);backface-visibility:hidden;background:linear-gradient(135deg,#1c65a7,#0c3158 72%);border:3px solid #d8b65f;border-radius:4px 9px 9px 4px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;box-shadow:inset 0 0 0 7px rgba(230,195,113,.12);animation:wise-cover-open 1.55s cubic-bezier(.62,.02,.24,1) .45s forwards}
    .wise-book-emblem{width:42px;height:42px;border:1px solid #e8cb7b;border-radius:50%;display:grid;place-items:center;color:#f5d98f;font:600 22px Georgia,serif}
    .wise-book-title{font:600 30px Georgia,serif;letter-spacing:.18em;color:#fff}
    .wise-book-subtitle{font:600 7px Arial,sans-serif;letter-spacing:.18em;color:#f2d78e}
    .wise-opening-hand{position:absolute;width:150px;right:2px;bottom:-2px;z-index:3;filter:drop-shadow(0 7px 5px rgba(0,0,0,.25));transform:translate(35px,35px) rotate(12deg);opacity:0;animation:wise-hand-open 1.35s ease-in-out .25s forwards}
    .wise-intro-caption{font:500 9px Arial,sans-serif;letter-spacing:.2em;text-align:center;color:#d8e5f0;padding:0 18px;animation:wise-caption-in .7s ease 1.2s both}
    .wise-intro-skip{position:absolute;right:22px;bottom:20px;background:transparent;border:1px solid rgba(255,255,255,.45);border-radius:30px;padding:9px 16px;color:#fff;font:500 11px Arial,sans-serif;cursor:pointer}
    .wise-intro-skip:hover{background:rgba(255,255,255,.12)}
    @keyframes wise-cover-open{0%{transform:rotateY(0)}75%,100%{transform:rotateY(-158deg)}}
    @keyframes wise-hand-open{0%{opacity:0;transform:translate(35px,35px) rotate(12deg)}20%{opacity:1}65%{transform:translate(-35px,-5px) rotate(-7deg)}100%{opacity:0;transform:translate(-65px,-12px) rotate(-13deg)}}
    @keyframes wise-caption-in{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
    @media(max-width:480px){.wise-book-stage{transform:scale(.88)}.wise-intro-brand{font-size:8px;letter-spacing:.17em}}
  `;

  document.head.appendChild(style);
  document.body.appendChild(intro);
  let finished = false;
  const closeIntro = () => {
    if (finished) return;
    finished = true;
    intro.classList.add('is-leaving');
    window.setTimeout(() => { intro.remove(); style.remove(); }, 700);
  };
  intro.querySelector('.wise-intro-skip').addEventListener('click', closeIntro);
  window.setTimeout(closeIntro, 2850);
})();


// Navigation dropdowns (supports touch/mobile)
document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
  const button = dropdown.querySelector('.nav-drop-toggle');
  button?.addEventListener('click', () => {
    const isOpen = dropdown.classList.toggle('open');
    button.setAttribute('aria-expanded', String(isOpen));
  });
});


// Count WISE achievement figures when the standalone statistics section enters view.
(() => {
  const counters = [...document.querySelectorAll('.glance-number [data-count], .glance-secondary [data-count]')];
  if (!counters.length) return;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const animateCounter = (el) => {
    if (el.dataset.counted === 'true') return;
    el.dataset.counted = 'true';
    const target = Number(el.dataset.count);
    const suffix = el.dataset.suffix || '';
    if (reducedMotion) { el.textContent = target.toLocaleString() + suffix; return; }
    const duration = 1500;
    const start = performance.now();
    const step = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(target * eased).toLocaleString() + suffix;
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };
  const section = document.querySelector('.wise-at-glance');
  if (!section) return;
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      if (entries.some(entry => entry.isIntersecting)) {
        counters.forEach(animateCounter);
        observer.disconnect();
      }
    }, { threshold: 0.25 });
    observer.observe(section);
  } else {
    counters.forEach(animateCounter);
  }
})();


// Accessible website search: find matching homepage sections and useful pages.
(() => {
  const toggle = document.querySelector('.site-search-toggle');
  const panel = document.querySelector('#site-search-panel');
  const form = document.querySelector('.site-search-form');
  const input = document.querySelector('#site-search-input');
  const results = document.querySelector('.site-search-results');
  const close = document.querySelector('.site-search-close');
  if (!toggle || !panel || !form || !input || !results || !close) return;

  const searchable = [...document.querySelectorAll('main section[id]')].map(section => {
    const heading = section.querySelector('h1,h2,h3');
    return {
      title: heading?.textContent.trim() || section.id.replace(/-/g, ' '),
      text: section.textContent.replace(/\s+/g, ' ').trim().slice(0, 260),
      href: '#' + section.id,
      content: section.textContent.toLowerCase()
    };
  });
  searchable.push(
    {title:'Student Result Login',text:'Access the student result portal.',href:'result-login.html',content:'student result portal login'},
    {title:'Teacher Login',text:'Open the WISE teacher login page.',href:'teacher-login.html',content:'teacher staff login'}
  );

  const closeSearch = () => {
    panel.hidden = true;
    toggle.setAttribute('aria-expanded','false');
  };
  toggle.addEventListener('click', () => {
    const opening = panel.hidden;
    panel.hidden = !opening;
    toggle.setAttribute('aria-expanded',String(opening));
    if (opening) input.focus();
  });
  close.addEventListener('click',closeSearch);
  form.addEventListener('submit',event => event.preventDefault());
  input.addEventListener('input',() => {
    const query = input.value.trim().toLowerCase();
    results.replaceChildren();
    if (!query) return;
    const matches = searchable.filter(item => (item.title+' '+item.content).includes(query)).slice(0,8);
    if (!matches.length) {
      const empty=document.createElement('p');
      empty.className='site-search-empty';
      empty.textContent='No matching pages or sections found. Try another keyword.';
      results.append(empty);
      return;
    }
    matches.forEach(item => {
      const link=document.createElement('a');
      link.className='site-search-result';
      link.href=item.href;
      const title=document.createElement('strong');
      title.textContent=item.title;
      const description=document.createElement('span');
      description.textContent=item.text;
      link.append(title,description);
      link.addEventListener('click',closeSearch);
      results.append(link);
    });
  });
  document.addEventListener('keydown',event => {
    if (event.key==='Escape' && !panel.hidden) {
      closeSearch();
      toggle.focus();
    }
  });
})();


// Reveal the Why Choose WISE cards with a staggered shuffle-deal effect.
(() => {
  const section = document.querySelector('.why-wise');
  if (!section) return;
  const cards = section.querySelectorAll('.why-wise-card');
  if (!cards.length) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches ||
      !('IntersectionObserver' in window)) return;
  section.classList.add('wise-shuffle-ready');
  const observer = new IntersectionObserver(entries => {
    if (entries.some(entry => entry.isIntersecting)) {
      section.classList.add('wise-shuffle-visible');
      observer.disconnect();
    }
  }, { threshold: 0.18 });
  observer.observe(section);
})();

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

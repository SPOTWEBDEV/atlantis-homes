/**
 * reviews.js — the "Write a Review" modal: open/close, the click-to-rate
 * star control, and AJAX submission. New reviews aren't injected into the
 * feed immediately since they land as "pending" — instead we show a
 * success message and close the modal, matching the moderation flow.
 */

const BASE = window.ATLANTIS_BASE_URL || '';

const modal = document.getElementById('review-modal');
const openBtn = document.getElementById('open-review-modal');
const closeBtn = document.getElementById('close-review-modal');
const backdrop = document.getElementById('review-modal-backdrop');
const form = document.getElementById('review-form');
const starWrap = document.getElementById('star-rating');
const ratingInput = document.getElementById('rating-input');
const formError = document.getElementById('form-error');
const formSuccess = document.getElementById('form-success');
const submitBtn = document.getElementById('review-submit-btn');

function openModal() {
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  document.body.style.overflow = '';
}

openBtn.addEventListener('click', openModal);
closeBtn.addEventListener('click', closeModal);
backdrop.addEventListener('click', closeModal);
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
});

// --- Star rating control ------------------------------------------------
starWrap.addEventListener('click', (e) => {
  const btn = e.target.closest('.star-btn');
  if (!btn) return;
  const value = Number(btn.dataset.value);
  ratingInput.value = value;

  starWrap.querySelectorAll('.star-btn').forEach((star) => {
    const isFilled = Number(star.dataset.value) <= value;
    star.classList.toggle('active', isFilled);
    star.setAttribute('aria-checked', String(isFilled && Number(star.dataset.value) === value));
  });
});

// --- Submit ---------------------------------------------------------------
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  formError.classList.add('hidden');
  formSuccess.classList.add('hidden');

  const payload = {
    rating: ratingInput.value,
    title: form.title.value,
    body: form.body.value,
    captcha: form.captcha.checked,
  };
  if (form.guest_name) payload.guest_name = form.guest_name.value;
  if (form.guest_email) payload.guest_email = form.guest_email.value;

  submitBtn.disabled = true;
  submitBtn.textContent = 'Submitting…';

  try {
    const res = await fetch(`${BASE}/api/submit_review.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (!data.ok) {
      formError.textContent = data.error;
      formError.classList.remove('hidden');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit Review';
      return;
    }

    formSuccess.textContent = data.message;
    formSuccess.classList.remove('hidden');
    form.reset();
    starWrap.querySelectorAll('.star-btn').forEach((s) => s.classList.remove('active'));
    ratingInput.value = '';
    submitBtn.textContent = 'Submit Review';
    submitBtn.disabled = false;

    setTimeout(closeModal, 1800);
  } catch (err) {
    formError.textContent = 'Something went wrong — please try again.';
    formError.classList.remove('hidden');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Submit Review';
  }
});

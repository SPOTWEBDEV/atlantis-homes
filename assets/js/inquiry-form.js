/**
 * inquiry-form.js — generic AJAX submit handler for any form that posts
 * to api/submit_inquiry.php. Used by book-a-session.php, contact.php, and
 * the "Request a Detailed Quote" form on estimate.php.
 *
 * Expects the form to carry data-inquiry-type="booking|contact|estimate"
 * and to contain fields named name, email, phone (optional),
 * property_id (optional), preferred_date (optional), message.
 * It also looks for sibling elements with [data-form-error] and
 * [data-form-success] to report status, and a submit button to disable
 * while the request is in flight.
 */
(function () {
  const BASE = window.ATLANTIS_BASE_URL || '';

  document.querySelectorAll('form[data-inquiry-type]').forEach((form) => {
    const errorEl = form.querySelector('[data-form-error]');
    const successEl = form.querySelector('[data-form-success]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalLabel = submitBtn ? submitBtn.textContent : '';

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorEl?.classList.add('hidden');
      successEl?.classList.add('hidden');

      const payload = { type: form.dataset.inquiryType };
      ['name', 'email', 'phone', 'property_id', 'investment_id', 'preferred_date', 'message', 'spec_details'].forEach((field) => {
        if (form.elements[field]) payload[field] = form.elements[field].value;
      });

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending…';
      }

      try {
        const res = await fetch(`${BASE}/api/submit_inquiry.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (!data.ok) {
          if (errorEl) {
            errorEl.textContent = data.error;
            errorEl.classList.remove('hidden');
          }
          return;
        }

        if (successEl) {
          successEl.textContent = data.message;
          successEl.classList.remove('hidden');
        }
        form.reset();
        form.dispatchEvent(new CustomEvent('inquiry:submitted'));
      } catch (err) {
        if (errorEl) {
          errorEl.textContent = 'Something went wrong — please try again.';
          errorEl.classList.remove('hidden');
        }
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalLabel;
        }
      }
    });
  });
})();

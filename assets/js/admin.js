/**
 * admin.js — shared by admin/reviews.php and admin/update-property.php.
 * Each page only has the DOM nodes relevant to it, so every block below
 * guards on the element existing before wiring anything up.
 */

const BASE = window.ATLANTIS_BASE_URL || '';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// ---------------------------------------------------------------------
// Review moderation (admin/reviews.php)
// ---------------------------------------------------------------------
const moderationTable = document.getElementById('moderation-table');
if (moderationTable) {
  const emptyState = document.getElementById('moderation-empty');

  moderationTable.addEventListener('click', async (e) => {
    const btn = e.target.closest('.approve-btn, .reject-btn');
    if (!btn) return;

    const row = btn.closest('tr');
    const id = row.dataset.id;
    const action = btn.classList.contains('approve-btn') ? 'approve' : 'reject';

    row.style.opacity = '0.4';
    row.querySelectorAll('button').forEach((b) => (b.disabled = true));

    try {
      const res = await fetch(`${BASE}/admin/actions/update_review.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ id, action }),
      });
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Could not update this review.');

      row.classList.add('fade-in');
      row.remove();

      if (!moderationTable.querySelector('tbody tr')) {
        emptyState.classList.remove('hidden');
      }
    } catch (err) {
      console.error(err);
      row.style.opacity = '1';
      row.querySelectorAll('button').forEach((b) => (b.disabled = false));
      alert(err.message);
    }
  });
}

// ---------------------------------------------------------------------
// Property Milestone Updater (admin/update-property.php)
// ---------------------------------------------------------------------
const propertySelect = document.getElementById('property-select');
if (propertySelect) {
  propertySelect.addEventListener('change', () => {
    window.location.href = `${BASE}/admin/update-property.php?id=${encodeURIComponent(propertySelect.value)}`;
  });
}

const updateForm = document.getElementById('update-form');
if (updateForm) {
  const errorEl = document.getElementById('update-error');
  const successEl = document.getElementById('update-success');
  const submitBtn = document.getElementById('update-submit-btn');
  const updatesLog = document.getElementById('updates-log');
  const updatesEmpty = document.getElementById('updates-empty');
  const currentStageBadge = document.getElementById('current-stage-badge');

  updateForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorEl.classList.add('hidden');
    successEl.classList.add('hidden');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting…';

    try {
      const res = await fetch(`${BASE}/admin/actions/update_property.php`, {
        method: 'POST',
        body: new FormData(updateForm), // includes the file input + csrf_token field
      });
      const data = await res.json();

      if (!data.ok) {
        errorEl.textContent = data.error;
        errorEl.classList.remove('hidden');
        return;
      }

      successEl.textContent = data.message;
      successEl.classList.remove('hidden');
      currentStageBadge.textContent = data.update.milestone;
      updatesEmpty.classList.add('hidden');

      const li = document.createElement('li');
      li.className = 'border-b border-white/5 pb-5 fade-in';
      li.innerHTML = `
        <div class="flex items-center justify-between text-xs text-slate mb-1.5">
          <span class="font-semibold text-gold">${escapeHtml(data.update.milestone)}</span>
          <span>${escapeHtml(data.update.created_label)}</span>
        </div>
        <p class="text-sm">${escapeHtml(data.update.note)}</p>
        ${data.update.photo_count > 0 ? `<p class="text-xs text-slate mt-1.5">${data.update.photo_count} photo${data.update.photo_count === 1 ? '' : 's'} attached</p>` : ''}
        <p class="text-xs text-slate mt-1">— ${escapeHtml(data.update.admin_name)}</p>
      `;
      updatesLog.prepend(li);

      updateForm.querySelector('textarea[name="note"]').value = '';
      updateForm.querySelector('#photos-input').value = '';
    } catch (err) {
      errorEl.textContent = 'Something went wrong — please try again.';
      errorEl.classList.remove('hidden');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Post Update';
    }
  });
}

// ---------------------------------------------------------------------
// Investor Ledger (admin/purchases.php)
// ---------------------------------------------------------------------
const purchaseModal = document.getElementById('purchase-modal');
if (purchaseModal) {
  const openBtn = document.getElementById('open-purchase-modal');
  const closeBtn = document.getElementById('close-purchase-modal');
  const backdrop = document.getElementById('purchase-modal-backdrop');
  const form = document.getElementById('create-purchase-form');
  const errorEl = document.getElementById('purchase-form-error');
  const successEl = document.getElementById('purchase-form-success');
  const propertySelect = document.getElementById('purchase-property-select');
  const totalPriceInput = document.getElementById('purchase-total-price');

  function openPurchaseModal() {
    purchaseModal.classList.remove('hidden');
    purchaseModal.classList.add('flex');
  }
  function closePurchaseModal() {
    purchaseModal.classList.add('hidden');
    purchaseModal.classList.remove('flex');
  }

  openBtn.addEventListener('click', openPurchaseModal);
  closeBtn.addEventListener('click', closePurchaseModal);
  backdrop.addEventListener('click', closePurchaseModal);

  // Pre-fill the contract price with the property's list price as a starting point
  propertySelect.addEventListener('change', () => {
    const opt = propertySelect.selectedOptions[0];
    if (opt && opt.dataset.price) totalPriceInput.value = opt.dataset.price;
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorEl.classList.add('hidden');
    successEl.classList.add('hidden');

    const payload = Object.fromEntries(new FormData(form).entries());

    try {
      const res = await fetch(`${BASE}/admin/actions/create_purchase.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (!data.ok) {
        errorEl.textContent = data.error;
        errorEl.classList.remove('hidden');
        return;
      }
      successEl.textContent = data.message;
      successEl.classList.remove('hidden');
      setTimeout(() => window.location.reload(), 900);
    } catch (err) {
      errorEl.textContent = 'Something went wrong — please try again.';
      errorEl.classList.remove('hidden');
    }
  });
}

const purchasesList = document.getElementById('purchases-list');
if (purchasesList) {
  purchasesList.addEventListener('click', (e) => {
    const btn = e.target.closest('.add-payment-btn');
    if (!btn) return;
    const card = btn.closest('.purchase-card');
    card.querySelector('.add-payment-form').classList.toggle('hidden');
  });

  purchasesList.addEventListener('submit', async (e) => {
    const form = e.target.closest('.add-payment-form');
    if (!form) return;
    e.preventDefault();

    const card = form.closest('.purchase-card');
    const errorEl = card.querySelector('.payment-form-error');
    errorEl.classList.add('hidden');

    const payload = Object.fromEntries(new FormData(form).entries());
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
      const res = await fetch(`${BASE}/admin/actions/add_payment.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Could not record this payment.');

      card.querySelector('.paid-amount').textContent = data.amount_paid;
      card.querySelector('.outstanding-amount').textContent = data.outstanding;
      card.querySelector('.payment-progress-bar').style.width = data.paid_pct + '%';
      form.reset();
      form.classList.add('hidden');
    } catch (err) {
      errorEl.textContent = err.message;
      errorEl.classList.remove('hidden');
    } finally {
      submitBtn.disabled = false;
    }
  });
}
// ---------------------------------------------------------------------
// Add/Edit Investment Opportunity form (admin/investment-form.php)
// ---------------------------------------------------------------------
const investmentForm = document.getElementById('investment-form');
if (investmentForm) {
  // Pill-style radio buttons: clicking the label should visually toggle
  // 'active' the same way the JS-driven type-pills elsewhere do.
  investmentForm.querySelectorAll('input[type="radio"][name="type"]').forEach((radio) => {
    radio.addEventListener('change', () => {
      investmentForm.querySelectorAll('label.type-pill').forEach((label) => {
        label.classList.toggle('active', label.querySelector('input').checked);
      });
    });
  });

  const invErrorEl = document.getElementById('investment-form-error');
  const invSuccessEl = document.getElementById('investment-form-success');
  const invSubmitBtn = investmentForm.querySelector('button[type="submit"]');

  investmentForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    invErrorEl.classList.add('hidden');
    invSuccessEl.classList.add('hidden');
    invSubmitBtn.disabled = true;

    try {
      const res = await fetch(`${BASE}/admin/actions/save_investment.php`, {
        method: 'POST',
        body: new FormData(investmentForm),
      });
      const data = await res.json();

      if (!data.ok) {
        invErrorEl.textContent = data.error;
        invErrorEl.classList.remove('hidden');
        invSubmitBtn.disabled = false;
        return;
      }

      invSuccessEl.textContent = data.message;
      invSuccessEl.classList.remove('hidden');
      setTimeout(() => {
        window.location.href = `${BASE}/admin/investments.php`;
      }, 900);
    } catch (err) {
      invErrorEl.textContent = 'Something went wrong — please try again.';
      invErrorEl.classList.remove('hidden');
      invSubmitBtn.disabled = false;
    }
  });
}

// ---------------------------------------------------------------------
// Delete Investment Opportunity (admin/investments.php)
// ---------------------------------------------------------------------
const investmentsTable = document.getElementById('investments-table');
if (investmentsTable) {
  investmentsTable.addEventListener('click', async (e) => {
    const btn = e.target.closest('.delete-investment-btn');
    if (!btn) return;

    const row = btn.closest('tr');
    const name = row.dataset.name;
    if (!confirm(`Delete "${name}"? This can't be undone.`)) return;

    btn.disabled = true;
    btn.textContent = 'Deleting…';

    try {
      const res = await fetch(`${BASE}/admin/actions/delete_investment.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: row.dataset.id, csrf_token: csrfToken }),
      });
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Could not delete this opportunity.');
      row.remove();
    } catch (err) {
      alert(err.message);
      btn.disabled = false;
      btn.textContent = 'Delete';
    }
  });
}

// ---------------------------------------------------------------------
const propertyForm = document.getElementById('property-form');
if (propertyForm) {
  const errorEl = document.getElementById('form-error');
  const successEl = document.getElementById('form-success');
  const submitBtn = propertyForm.querySelector('button[type="submit"]');

  propertyForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorEl.classList.add('hidden');
    successEl.classList.add('hidden');
    submitBtn.disabled = true;

    try {
      const res = await fetch(`${BASE}/admin/actions/save_property.php`, {
        method: 'POST',
        body: new FormData(propertyForm), // includes file inputs + csrf_token field
      });
      const data = await res.json();

      if (!data.ok) {
        errorEl.textContent = data.error;
        errorEl.classList.remove('hidden');
        submitBtn.disabled = false;
        return;
      }

      successEl.textContent = data.message;
      successEl.classList.remove('hidden');
      setTimeout(() => {
        window.location.href = `${BASE}/admin/properties.php`;
      }, 900);
    } catch (err) {
      errorEl.textContent = 'Something went wrong — please try again.';
      errorEl.classList.remove('hidden');
      submitBtn.disabled = false;
    }
  });
}

// ---------------------------------------------------------------------
// Delete Property (admin/properties.php)
// ---------------------------------------------------------------------
const propertiesTable = document.getElementById('properties-table');
if (propertiesTable) {
  propertiesTable.addEventListener('click', async (e) => {
    const btn = e.target.closest('.delete-property-btn');
    if (!btn) return;

    const row = btn.closest('tr');
    const name = row.dataset.name;
    if (!confirm(`Delete "${name}"? This also removes its purchases, payments, and update history. This can't be undone.`)) {
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Deleting…';

    try {
      const res = await fetch(`${BASE}/admin/actions/delete_property.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: row.dataset.id, csrf_token: csrfToken }),
      });
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Could not delete this property.');
      row.remove();
    } catch (err) {
      alert(err.message);
      btn.disabled = false;
      btn.textContent = 'Delete';
    }
  });
}

// ---------------------------------------------------------------------
// Inquiries (admin/inquiries.php)
// ---------------------------------------------------------------------
const inquiriesList = document.getElementById('inquiries-list');
if (inquiriesList) {
  inquiriesList.addEventListener('click', async (e) => {
    const btn = e.target.closest('.toggle-status-btn');
    if (!btn) return;

    const card = btn.closest('.inquiry-card');
    const badge = card.querySelector('.status-badge');
    const isContacted = badge.textContent.trim() === 'contacted';
    const newStatus = isContacted ? 'new' : 'contacted';

    btn.disabled = true;
    try {
      const res = await fetch(`${BASE}/admin/actions/update_inquiry.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ id: card.dataset.id, status: newStatus, csrf_token: csrfToken }),
      });
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Could not update this inquiry.');

      badge.textContent = data.status;
      badge.className = `status-badge text-[11px] font-semibold uppercase tracking-wide rounded-full px-2.5 py-1 ${data.status === 'contacted' ? 'bg-green-500/15 text-green-300' : 'bg-white/10 text-slate'}`;
      btn.textContent = data.status === 'contacted' ? 'Mark as New' : 'Mark Contacted';
    } catch (err) {
      alert(err.message);
    } finally {
      btn.disabled = false;
    }
  });
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

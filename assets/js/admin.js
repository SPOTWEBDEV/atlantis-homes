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

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

/**
 * portfolio.js
 * Handles the AJAX-powered filter bar and the expandable, tabbed detail
 * panel on each property card. The grid is re-rendered entirely on every
 * filter click, so all interactivity below is delegated to #properties-grid
 * rather than bound to individual cards.
 */

const grid = document.getElementById('properties-grid');
const filterBar = document.getElementById('filter-bar');
const resultsCount = document.getElementById('results-count');
const emptyState = document.getElementById('empty-state');

const detailCache = new Map(); // property id -> detail payload, avoids re-fetching a tab you've already opened

// ---------------------------------------------------------------------
// Card template — mirrors render_property_card_php() in portfolio.php
// ---------------------------------------------------------------------
function renderCard(p) {
  return `
    <article id="property-${p.id}" class="property-card border border-white/10 rounded-2xl overflow-hidden bg-obsidian-card hover:border-gold/40 transition-colors" data-id="${p.id}" data-type="${p.type}">
      <div class="relative h-52 overflow-hidden">
        <img src="${p.image_url}" alt="${escapeHtml(p.name)}" class="w-full h-full object-cover">
        <span class="absolute top-4 left-4 text-xs font-semibold bg-obsidian/80 border border-gold/40 text-gold rounded-full px-3 py-1 backdrop-blur-sm">${escapeHtml(p.type_label)}</span>
      </div>
      <div class="p-6">
        <h3 class="font-display text-xl">${escapeHtml(p.name)}</h3>
        <p class="text-slate text-sm mt-1">${escapeHtml(p.location)}</p>
        <div class="mt-4 flex items-center gap-4 text-xs text-slate">
          <span>${p.bedrooms} Beds</span><span>&middot;</span><span>${p.bathrooms} Baths</span><span>&middot;</span><span>${p.size_sqm} sqm</span>
        </div>
        <div class="mt-4 flex items-center justify-between">
          <span class="text-gold font-semibold">${p.price}</span>
          <span class="text-xs text-slate">${p.roi_10yr_pct}% proj. / 10yr</span>
        </div>
        <button type="button" class="view-details-btn mt-5 w-full text-sm font-semibold border border-white/15 hover:border-gold hover:text-gold rounded-full py-2.5 transition-colors" data-id="${p.id}" aria-expanded="false">
          View Details
        </button>
      </div>
      <div class="detail-panel hidden border-t border-white/10 p-6" data-loaded="false">
        <div class="flex gap-2 text-sm" role="tablist">
          <button type="button" class="tab-btn active" data-tab="overview" role="tab">Overview</button>
          <button type="button" class="tab-btn" data-tab="floorplan" role="tab">3D Floor Plan</button>
          <button type="button" class="tab-btn" data-tab="amenities" role="tab">Amenities</button>
        </div>
        <div class="tab-content mt-5 text-sm text-slate leading-relaxed min-h-[120px]">
          <div class="loading-spinner flex items-center gap-2 text-slate text-sm"><span class="pulse-dot">&#9679;</span> Loading details&hellip;</div>
        </div>
      </div>
    </article>
  `;
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

// ---------------------------------------------------------------------
// Filter bar — fetches the matching set and re-renders the whole grid
// ---------------------------------------------------------------------
filterBar.addEventListener('click', async (e) => {
  const btn = e.target.closest('.filter-btn');
  if (!btn) return;

  filterBar.querySelectorAll('.filter-btn').forEach((b) => b.classList.remove('active'));
  btn.classList.add('active');

  grid.style.opacity = '0.4';
  try {
    const res = await fetch(`/api/get_properties.php?type=${encodeURIComponent(btn.dataset.type)}`);
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || 'Could not load properties');

    grid.innerHTML = data.properties.map(renderCard).join('');
    resultsCount.textContent = `${data.count} ${data.count === 1 ? 'property' : 'properties'}`;
    emptyState.classList.toggle('hidden', data.count > 0);
  } catch (err) {
    console.error(err);
    resultsCount.textContent = 'Could not load properties — please try again.';
  } finally {
    grid.style.opacity = '1';
  }
});

// ---------------------------------------------------------------------
// Card expansion + tabs (event-delegated so it survives grid re-renders)
// ---------------------------------------------------------------------
grid.addEventListener('click', async (e) => {
  const viewBtn = e.target.closest('.view-details-btn');
  const tabBtn = e.target.closest('.tab-btn');

  if (viewBtn) {
    const card = viewBtn.closest('.property-card');
    const panel = card.querySelector('.detail-panel');
    const isOpen = !panel.classList.contains('hidden');

    panel.classList.toggle('hidden');
    viewBtn.textContent = isOpen ? 'View Details' : 'Hide Details';
    viewBtn.setAttribute('aria-expanded', String(!isOpen));

    if (!isOpen && panel.dataset.loaded === 'false') {
      await loadDetail(card.dataset.id, panel);
    }
    return;
  }

  if (tabBtn) {
    const panel = tabBtn.closest('.detail-panel');
    panel.querySelectorAll('.tab-btn').forEach((b) => b.classList.remove('active'));
    tabBtn.classList.add('active');
    renderTabContent(panel, tabBtn.dataset.tab);
  }
});

async function loadDetail(id, panel) {
  const content = panel.querySelector('.tab-content');
  try {
    let detail = detailCache.get(id);
    if (!detail) {
      const res = await fetch(`/api/get_property_detail.php?id=${encodeURIComponent(id)}`);
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'Could not load this property');
      detail = data.property;
      detailCache.set(id, detail);
    }
    panel.detail = detail;
    panel.dataset.loaded = 'true';
    renderTabContent(panel, 'overview');
  } catch (err) {
    content.innerHTML = `<p class="text-red-400">${escapeHtml(err.message)}</p>`;
  }
}

function renderTabContent(panel, tab) {
  const detail = panel.detail;
  const content = panel.querySelector('.tab-content');
  if (!detail) return;

  if (tab === 'overview') {
    content.innerHTML = `
      <p>${escapeHtml(detail.overview)}</p>
      <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-slate">
        <span>Current stage: <span class="text-gold font-semibold">${escapeHtml(detail.milestone_stage)}</span></span>
        <span>5-yr projected ROI: <span class="text-gold font-semibold">${detail.roi_5yr_pct}%</span></span>
        <span>10-yr projected ROI: <span class="text-gold font-semibold">${detail.roi_10yr_pct}%</span></span>
      </div>
    `;
  } else if (tab === 'floorplan') {
    content.innerHTML = `
      <img src="${detail.floor_plan_url}" alt="Floor plan for ${escapeHtml(detail.name)}" class="w-full h-56 object-cover rounded-xl border border-white/10">
      <p class="mt-3 text-xs text-slate">Illustrative layout. Final unit dimensions confirmed at reservation.</p>
    `;
  } else if (tab === 'amenities') {
    content.innerHTML = `<div class="flex flex-wrap gap-2">${detail.amenities.map((a) => `<span class="amenity-pill">${escapeHtml(a)}</span>`).join('')}</div>`;
  }
}

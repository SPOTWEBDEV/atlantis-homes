/**
 * estimate.js — Build Cost Estimator.
 *
 * Cost model: each line item has a base rate in ₦ per square metre,
 * roughly reflecting typical 2026 Nigerian construction costs at a
 * "Standard" finish in Lagos. That base is then scaled by:
 *   - finishMultiplier   (Standard / Premium / Luxury material & labour grade)
 *   - locationMultiplier (regional cost-of-construction index)
 *   - floorMultiplier    (each additional storey adds structural complexity)
 *
 * Architectural/engineering fees and a contingency allowance are added
 * on top of the line-item subtotal, mirroring how a real construction
 * quote is usually structured.
 */

const RATE_PER_SQM = {
  // category label -> base ₦ per sqm at Standard finish, Lagos, single storey
  'Foundation & Substructure': 22000,
  'Structural Framing (Block & Concrete Work)': 42000,
  'Roofing': 18000,
  'Electrical Wiring & Fittings': 11000,
  'Plumbing & Water Systems': 9500,
  'Walls, Plastering & Screeding': 16000,
  'Doors, Windows & Glazing': 14000,
  'Flooring & Tiling': 13000,
  'Painting & Finishing': 7500,
  'Kitchen & Fittings': 9000,
  'Landscaping & External Works': 5500,
};

const FINISH_MULTIPLIER = { standard: 1.0, premium: 1.35, luxury: 1.8 };
const LOCATION_MULTIPLIER = { lagos: 1.15, abuja: 1.1, 'port-harcourt': 1.0, other: 0.92 };

const sqmSlider = document.getElementById('sqm-slider');
const sqmDisplay = document.getElementById('sqm-display');
const bedroomsSelect = document.getElementById('bedrooms-select');
const floorsSelect = document.getElementById('floors-select');
const finishSelector = document.getElementById('finish-selector');
const locationSelector = document.getElementById('location-selector');
const breakdownList = document.getElementById('breakdown-list');
const subtotalEl = document.getElementById('subtotal-value');
const feesEl = document.getElementById('fees-value');
const contingencyEl = document.getElementById('contingency-value');
const grandTotalEl = document.getElementById('grand-total');
const messageField = document.getElementById('estimate-message-field');

let state = {
  sqm: Number(sqmSlider.value),
  bedrooms: bedroomsSelect.value,
  floors: Number(floorsSelect.value),
  finish: 'standard',
  location: 'lagos',
};

function formatNaira(value) {
  return '₦' + Math.round(value).toLocaleString('en-NG');
}

function updateSliderFill(slider) {
  const pct = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
  slider.style.setProperty('--fill', pct + '%');
}

function calculate() {
  const finishMult = FINISH_MULTIPLIER[state.finish];
  const locationMult = LOCATION_MULTIPLIER[state.location];
  const floorMult = 1 + (state.floors - 1) * 0.06; // taller builds cost a bit more per sqm

  const lineItems = Object.entries(RATE_PER_SQM).map(([label, baseRate]) => {
    const amount = baseRate * state.sqm * finishMult * locationMult * floorMult;
    return { label, amount };
  });

  const subtotal = lineItems.reduce((sum, item) => sum + item.amount, 0);
  const fees = subtotal * 0.08;
  const contingency = subtotal * 0.05;
  const grandTotal = subtotal + fees + contingency;

  return { lineItems, subtotal, fees, contingency, grandTotal };
}

function render() {
  const { lineItems, subtotal, fees, contingency, grandTotal } = calculate();
  const maxAmount = Math.max(...lineItems.map((i) => i.amount));

  breakdownList.innerHTML = lineItems
    .map(
      (item) => `
      <div class="py-3.5">
        <div class="flex justify-between text-sm mb-1.5">
          <span>${item.label}</span>
          <span class="font-medium text-gold">${formatNaira(item.amount)}</span>
        </div>
        <div class="h-1.5 rounded-full bg-white/5 overflow-hidden">
          <div class="h-full bg-gold/60" style="width:${(item.amount / maxAmount) * 100}%"></div>
        </div>
      </div>
    `
    )
    .join('');

  subtotalEl.textContent = formatNaira(subtotal);
  feesEl.textContent = formatNaira(fees);
  contingencyEl.textContent = formatNaira(contingency);
  grandTotalEl.textContent = formatNaira(grandTotal);

  // Keep the hidden "Request a Detailed Quote" message field in sync so a
  // submission carries the exact spec and numbers the user was looking at.
  if (messageField) {
    const finishLabel = state.finish[0].toUpperCase() + state.finish.slice(1);
    const locationLabel = state.location.replace('-', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    const lines = lineItems.map((i) => `- ${i.label}: ${formatNaira(i.amount)}`).join('\n');
    messageField.value =
      `Build estimate request:\n` +
      `${state.bedrooms}-bedroom, ${state.floors}-floor home, ${state.sqm} sqm, ${finishLabel} finish, ${locationLabel}.\n\n` +
      `Itemised estimate:\n${lines}\n\n` +
      `Subtotal: ${formatNaira(subtotal)}\nFees (8%): ${formatNaira(fees)}\nContingency (5%): ${formatNaira(contingency)}\n` +
      `Grand Total: ${formatNaira(grandTotal)}\n\nPlease send a formal, fixed-price quote for this specification.`;
  }
}

sqmSlider.addEventListener('input', () => {
  state.sqm = Number(sqmSlider.value);
  sqmDisplay.textContent = state.sqm.toLocaleString('en-NG') + ' sqm';
  updateSliderFill(sqmSlider);
  render();
});

bedroomsSelect.addEventListener('change', () => {
  state.bedrooms = bedroomsSelect.value;
  render();
});

floorsSelect.addEventListener('change', () => {
  state.floors = Number(floorsSelect.value);
  render();
});

finishSelector.addEventListener('click', (e) => {
  const btn = e.target.closest('.type-pill');
  if (!btn) return;
  finishSelector.querySelectorAll('.type-pill').forEach((b) => b.classList.remove('active'));
  btn.classList.add('active');
  state.finish = btn.dataset.finish;
  render();
});

locationSelector.addEventListener('click', (e) => {
  const btn = e.target.closest('.type-pill');
  if (!btn) return;
  locationSelector.querySelectorAll('.type-pill').forEach((b) => b.classList.remove('active'));
  btn.classList.add('active');
  state.location = btn.dataset.location;
  render();
});

// Initial paint
updateSliderFill(sqmSlider);
render();

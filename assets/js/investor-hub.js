/**
 * investor-hub.js — Smart Investment ROI calculator.
 *
 * Model:
 *   - Capital appreciation compounds annually. The annual rate is derived
 *     from the development type's average 5-year total ROI (seeded from
 *     real listing data via window.ATLANTIS_RATES), so a slider move on
 *     "Off-Plan" vs "Completed" actually changes the curve, not just the cosmetics.
 *   - Rental income is added as simple (non-compounding) annual cash flow:
 *     amount * yieldRate * years — representing yield paid out, not reinvested.
 *   - Total projected value = principal + compounded capital gain + cumulative rental income.
 */

const amountSlider = document.getElementById('amount-slider');
const amountDisplay = document.getElementById('amount-display');
const yieldSlider = document.getElementById('yield-slider');
const yieldDisplay = document.getElementById('yield-display');
const typeSelector = document.getElementById('type-selector');
const typeLabelInline = document.getElementById('type-label-inline');

const TYPE_LABELS = { 'off-plan': 'off-plan', 'under-construction': 'under-construction', 'completed': 'completed' };

let state = {
  amount: Number(amountSlider.value),
  yieldRate: Number(yieldSlider.value) / 100,
  type: 'off-plan',
};

function formatNaira(value) {
  return '₦' + Math.round(value).toLocaleString('en-NG');
}

function updateSliderFill(slider) {
  const pct = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
  slider.style.setProperty('--fill', pct + '%');
}

/** Annual compounding rate implied by the type's average 5-year total ROI. */
function annualCapRateForType(type) {
  const rates = window.ATLANTIS_RATES?.[type];
  const roi5 = rates ? rates.roi5 : 35; // sensible fallback if data is missing
  return Math.pow(1 + roi5 / 100, 1 / 5) - 1;
}

function projectAt(years) {
  const annualCapRate = annualCapRateForType(state.type);
  const capitalGain = state.amount * (Math.pow(1 + annualCapRate, years) - 1);
  const rentalIncome = state.amount * state.yieldRate * years;
  const totalValue = state.amount + capitalGain + rentalIncome;
  const totalReturnPct = (totalValue / state.amount - 1) * 100;
  return { capitalGain, rentalIncome, totalValue, totalReturnPct };
}

function renderBars(p5, p10) {
  const maxTotal = Math.max(p5.capitalGain + p5.rentalIncome, p10.capitalGain + p10.rentalIncome, 1);

  function barRow(label, p) {
    const capPct = (p.capitalGain / maxTotal) * 100;
    const rentPct = (p.rentalIncome / maxTotal) * 100;
    return `
      <div>
        <div class="flex justify-between text-xs text-slate mb-1.5">
          <span class="font-medium text-white">${label}</span>
          <span>${formatNaira(p.capitalGain + p.rentalIncome)} gain</span>
        </div>
        <div class="flex h-3 rounded-full overflow-hidden bg-white/5">
          <div style="width:${capPct}%" class="bg-gold" title="Capital appreciation: ${formatNaira(p.capitalGain)}"></div>
          <div style="width:${rentPct}%" class="bg-gold/40" title="Rental income: ${formatNaira(p.rentalIncome)}"></div>
        </div>
      </div>
    `;
  }

  document.getElementById('roi-chart').innerHTML = `
    ${barRow('5-Year Horizon', p5)}
    ${barRow('10-Year Horizon', p10)}
    <div class="flex gap-5 text-xs text-slate pt-1">
      <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gold inline-block"></span> Capital appreciation</span>
      <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gold/40 inline-block"></span> Rental income</span>
    </div>
  `;
}

function render() {
  const p1 = projectAt(1);
  const p5 = projectAt(5);
  const p10 = projectAt(10);

  document.getElementById('result-5yr').textContent = formatNaira(p5.totalValue);
  document.getElementById('result-5yr-pct').textContent = p5.totalReturnPct.toFixed(1);
  document.getElementById('result-10yr').textContent = formatNaira(p10.totalValue);
  document.getElementById('result-10yr-pct').textContent = p10.totalReturnPct.toFixed(1);

  document.getElementById('year1-value').textContent = formatNaira(p1.totalValue);
  document.getElementById('year5-value').textContent = formatNaira(p5.totalValue);
  document.getElementById('year10-value').textContent = formatNaira(p10.totalValue);

  renderBars(p5, p10);
}

amountSlider.addEventListener('input', () => {
  state.amount = Number(amountSlider.value);
  amountDisplay.textContent = formatNaira(state.amount);
  updateSliderFill(amountSlider);
  render();
});

yieldSlider.addEventListener('input', () => {
  state.yieldRate = Number(yieldSlider.value) / 100;
  yieldDisplay.textContent = Number(yieldSlider.value).toFixed(1) + '%';
  updateSliderFill(yieldSlider);
  render();
});

typeSelector.addEventListener('click', (e) => {
  const btn = e.target.closest('.type-pill');
  if (!btn) return;
  typeSelector.querySelectorAll('.type-pill').forEach((b) => b.classList.remove('active'));
  btn.classList.add('active');
  state.type = btn.dataset.type;
  typeLabelInline.textContent = TYPE_LABELS[state.type] || state.type;
  render();
});

// Initial paint
updateSliderFill(amountSlider);
updateSliderFill(yieldSlider);
render();

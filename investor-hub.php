<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Investor Hub';
$activeNav = 'investor-hub';

// Feed the calculator real yield assumptions per property type, pulled
// from the same data the portfolio uses, so projections stay consistent
// with what's actually listed.
$typeRates = get_db()->query("
    SELECT type, ROUND(AVG(roi_5yr_pct),1) AS avg_5yr, ROUND(AVG(roi_10yr_pct),1) AS avg_10yr
    FROM properties GROUP BY type
")->fetchAll();
$rateMap = [];
foreach ($typeRates as $row) {
    $rateMap[$row['type']] = ['roi5' => (float) $row['avg_5yr'], 'roi10' => (float) $row['avg_10yr']];
}

require __DIR__ . '/includes/header.php';
?>

<section class="pt-36 pb-16 max-w-7xl mx-auto px-6 lg:px-10">
  <p data-reveal class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Smart Investor Hub</p>
  <h1 data-reveal data-reveal-delay="100" class="font-display text-4xl sm:text-5xl max-w-2xl">Model your return before you reserve a unit.</h1>
  <p data-reveal data-reveal-delay="150" class="mt-4 text-slate max-w-xl">Move the sliders to match a real investment scenario — the projection updates instantly using our current yield assumptions per development type.</p>
</section>

<section class="max-w-7xl mx-auto px-6 lg:px-10 pb-24 grid lg:grid-cols-5 gap-10">

  <!-- Calculator controls -->
  <div class="lg:col-span-2 border border-white/10 rounded-3xl p-8 bg-obsidian-card">
    <h2 class="font-display text-2xl mb-8">Your Scenario</h2>

    <label class="block mb-8">
      <span class="flex items-center justify-between text-sm font-medium mb-3">
        <span>Investment Amount</span>
        <span id="amount-display" class="text-gold font-semibold">₦25,000,000</span>
      </span>
      <input type="range" id="amount-slider" class="gold-slider" min="5000000" max="500000000" step="5000000" value="25000000">
      <span class="flex justify-between text-xs text-slate mt-2"><span>₦5M</span><span>₦500M</span></span>
    </label>

    <fieldset class="mb-8">
      <legend class="text-sm font-medium mb-3">Development Type</legend>
      <div class="grid grid-cols-3 gap-2" id="type-selector">
        <button type="button" data-type="off-plan" class="type-pill active">Off-Plan</button>
        <button type="button" data-type="under-construction" class="type-pill">Under Construction</button>
        <button type="button" data-type="completed" class="type-pill">Completed</button>
      </div>
    </fieldset>

    <label class="block">
      <span class="flex items-center justify-between text-sm font-medium mb-3">
        <span>Expected Annual Rental Yield</span>
        <span id="yield-display" class="text-gold font-semibold">7.5%</span>
      </span>
      <input type="range" id="yield-slider" class="gold-slider" min="3" max="14" step="0.5" value="7.5">
      <span class="flex justify-between text-xs text-slate mt-2"><span>3%</span><span>14%</span></span>
    </label>

    <p class="text-xs text-slate mt-8 leading-relaxed">Capital appreciation assumptions are derived from current average projected ROI across listed <span id="type-label-inline">off-plan</span> developments. Rental yield is layered on top and compounds annually for the projection below.</p>
  </div>

  <!-- Results -->
  <div class="lg:col-span-3 border border-white/10 rounded-3xl p-8 bg-gradient-to-br from-gold/10 to-transparent">
    <h2 class="font-display text-2xl mb-8">Projected Outcome</h2>

    <div class="grid sm:grid-cols-2 gap-6 mb-10">
      <div class="border border-white/10 rounded-2xl p-6">
        <p class="text-xs uppercase tracking-wider text-slate mb-2">5-Year Projection</p>
        <p id="result-5yr" class="font-display text-3xl text-gold">₦0</p>
        <p class="text-xs text-slate mt-2"><span id="result-5yr-pct">0</span>% total return</p>
      </div>
      <div class="border border-white/10 rounded-2xl p-6">
        <p class="text-xs uppercase tracking-wider text-slate mb-2">10-Year Projection</p>
        <p id="result-10yr" class="font-display text-3xl text-gold">₦0</p>
        <p class="text-xs text-slate mt-2"><span id="result-10yr-pct">0</span>% total return</p>
      </div>
    </div>

    <div class="mb-10">
      <p class="text-sm font-medium mb-4">Capital appreciation vs. rental income, over time</p>
      <div id="roi-chart" class="space-y-4" aria-hidden="true"><!-- bars rendered by investor-hub.js --></div>
    </div>

    <div class="grid grid-cols-3 text-center text-xs text-slate border-t border-white/10 pt-6">
      <div><p class="text-white font-semibold">Year 1</p><p id="year1-value" class="mt-1">₦0</p></div>
      <div><p class="text-white font-semibold">Year 5</p><p id="year5-value" class="mt-1">₦0</p></div>
      <div><p class="text-white font-semibold">Year 10</p><p id="year10-value" class="mt-1">₦0</p></div>
    </div>

    <a href="/portfolio.php" class="inline-block mt-8 text-sm font-semibold text-gold hover:text-gold-light">Browse matching properties &rarr;</a>
  </div>
</section>

<script>
  // Server-computed average yield assumptions per development type,
  // handed to investor-hub.js so the math stays anchored to real listings.
  window.ATLANTIS_RATES = <?= json_encode($rateMap) ?>;
</script>

<?php
$pageScripts = ['/assets/js/investor-hub.js'];
require __DIR__ . '/includes/footer.php';
?>

<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Build Estimate';
$activeNav = 'estimate';
$user = current_user();

require __DIR__ . '/includes/header.php';
?>

<section class="pt-36 pb-12 max-w-7xl mx-auto px-6 lg:px-10">
  <p data-reveal class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Build Cost Estimator</p>
  <h1 data-reveal data-reveal-delay="100" class="font-display text-4xl sm:text-5xl max-w-2xl">Describe the house you want. We'll price it out.</h1>
  <p data-reveal data-reveal-delay="150" class="mt-4 text-slate max-w-xl">Adjust the specs below for an instant, itemised construction estimate — from foundation to final paint. Figures are planning estimates; request a detailed quote for a fixed-price contract.</p>
</section>

<section class="max-w-7xl mx-auto px-6 lg:px-10 pb-16 grid lg:grid-cols-5 gap-10">

  <!-- Spec controls -->
  <div class="lg:col-span-2 border border-white/10 rounded-3xl p-8 bg-obsidian-card">
    <h2 class="font-display text-2xl mb-8">Your Specification</h2>

    <label class="block mb-8">
      <span class="flex items-center justify-between text-sm font-medium mb-3">
        <span>Total Build Area</span>
        <span id="sqm-display" class="text-gold font-semibold">220 sqm</span>
      </span>
      <input type="range" id="sqm-slider" class="gold-slider" min="60" max="1200" step="10" value="220">
      <span class="flex justify-between text-xs text-slate mt-2"><span>60 sqm</span><span>1,200 sqm</span></span>
    </label>

    <div class="grid grid-cols-2 gap-5 mb-8">
      <label class="block text-sm">
        <span class="block font-medium mb-2">Bedrooms</span>
        <select id="bedrooms-select" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
          <option value="1">1</option><option value="2">2</option><option value="3" selected>3</option>
          <option value="4">4</option><option value="5">5</option><option value="6">6+</option>
        </select>
      </label>
      <label class="block text-sm">
        <span class="block font-medium mb-2">Floors</span>
        <select id="floors-select" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
          <option value="1" selected>1 (Bungalow)</option>
          <option value="2">2</option>
          <option value="3">3+</option>
        </select>
      </label>
    </div>

    <fieldset class="mb-8">
      <legend class="text-sm font-medium mb-3">Building Type</legend>
      <div class="grid grid-cols-2 gap-2" id="building-type-selector">
        <button type="button" data-building-type="bungalow" class="type-pill active">Bungalow</button>
        <button type="button" data-building-type="duplex" class="type-pill">Duplex</button>
        <button type="button" data-building-type="terrace" class="type-pill">Terrace House</button>
        <button type="button" data-building-type="semi-detached" class="type-pill">Semi-Detached</button>
        <button type="button" data-building-type="detached" class="type-pill">Detached House</button>
        <button type="button" data-building-type="block-of-flats" class="type-pill">Block of Flats</button>
      </div>
    </fieldset>

    <fieldset class="mb-8">
      <legend class="text-sm font-medium mb-3">Finish Level</legend>
      <div class="grid grid-cols-3 gap-2" id="finish-selector">
        <button type="button" data-finish="standard" class="type-pill active">Standard</button>
        <button type="button" data-finish="premium" class="type-pill">Premium</button>
        <button type="button" data-finish="luxury" class="type-pill">Luxury</button>
      </div>
    </fieldset>

    <fieldset>
      <legend class="text-sm font-medium mb-3">Location</legend>
      <div class="grid grid-cols-2 gap-2" id="location-selector">
        <button type="button" data-location="lagos" class="type-pill active">Lagos</button>
        <button type="button" data-location="abuja" class="type-pill">Abuja</button>
        <button type="button" data-location="port-harcourt" class="type-pill">Port Harcourt</button>
        <button type="button" data-location="other" class="type-pill">Other (Nigeria)</button>
      </div>
    </fieldset>

    <p class="text-xs text-slate mt-8 leading-relaxed">Estimate based on typical 2026 per-square-metre construction rates in Nigeria, adjusted for finish quality, building height, and regional cost index. Land cost and title/legal fees are excluded.</p>
  </div>

  <!-- Breakdown -->
  <div class="lg:col-span-3 border border-white/10 rounded-3xl p-8 bg-gradient-to-br from-gold/10 to-transparent">
    <div class="flex items-end justify-between mb-6">
      <h2 class="font-display text-2xl">Itemised Estimate</h2>
      <p class="text-right"><span class="text-xs text-slate block">Grand Total</span><span id="grand-total" class="font-display text-3xl text-gold">₦0</span></p>
    </div>

    <div id="breakdown-list" class="divide-y divide-white/10 border-y border-white/10"><!-- rows rendered by estimate.js --></div>

    <div class="mt-6 space-y-2 text-sm">
      <div class="flex justify-between text-slate"><span>Construction Subtotal</span><span id="subtotal-value">₦0</span></div>
      <div class="flex justify-between text-slate"><span>Architectural &amp; Engineering Fees (8%)</span><span id="fees-value">₦0</span></div>
      <div class="flex justify-between text-slate"><span>Contingency Allowance (5%)</span><span id="contingency-value">₦0</span></div>
    </div>
  </div>
</section>

<!-- ================= REQUEST A DETAILED QUOTE ================= -->
<section class="max-w-3xl mx-auto px-6 lg:px-10 pb-24">
  <div data-reveal class="border border-white/10 rounded-3xl p-8 bg-obsidian-card">
    <h2 class="font-display text-2xl mb-2">Want a fixed-price quote?</h2>
    <p class="text-slate text-sm mb-6">Send us this specification and a project consultant will follow up with a formal, itemised quote within two business days.</p>

    <form id="estimate-quote-form" data-inquiry-type="estimate" novalidate>
      <input type="hidden" name="message" id="estimate-message-field">
      <input type="hidden" name="spec_details" id="estimate-spec-details-field">
      <div class="grid sm:grid-cols-2 gap-5 mb-5">
        <label class="block text-sm">
          <span class="block font-medium mb-1.5">Full Name</span>
          <input type="text" name="name" required value="<?= $user ? h($user['name']) : '' ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        </label>
        <label class="block text-sm">
          <span class="block font-medium mb-1.5">Email</span>
          <input type="email" name="email" required value="<?= $user ? h($user['email']) : '' ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        </label>
      </div>
      <label class="block text-sm mb-6">
        <span class="block font-medium mb-1.5">Phone <span class="text-slate">(optional)</span></span>
        <input type="tel" name="phone" placeholder="+234" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      </label>

      <p data-form-error class="hidden text-sm text-red-400 mb-4" role="alert"></p>
      <p data-form-success class="hidden text-sm text-gold mb-4" role="status"></p>

      <button type="submit" class="w-full bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Request a Detailed Quote</button>
    </form>
  </div>
</section>

<?php
$pageScripts = ['assets/js/estimate.js', 'assets/js/inquiry-form.js'];
require __DIR__ . '/includes/footer.php';
?>

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

$allProperties = get_db()->query("SELECT id, name, type, location FROM properties ORDER BY name ASC")->fetchAll();
$companyOpportunities = get_db()->query("SELECT * FROM investment_opportunities WHERE type = 'company' ORDER BY created_at DESC")->fetchAll();
$propertyOpportunities = get_db()->query("SELECT * FROM investment_opportunities WHERE type = 'property' ORDER BY created_at DESC")->fetchAll();
$user = current_user();

$requestedInvestmentIds = [];
if ($user) {
    $stmt = get_db()->prepare("SELECT investment_id FROM inquiries WHERE type = 'investment' AND user_id = ? AND investment_id IS NOT NULL");
    $stmt->execute([$user['id']]);
    $requestedInvestmentIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
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

    <a href="<?= base_url('portfolio.php') ?>" class="inline-block mt-8 text-sm font-semibold text-gold hover:text-gold-light">Browse matching properties &rarr;</a>
  </div>
</section>

<!-- ================= INVEST IN ATLANTIS HOMES ================= -->
<section class="max-w-7xl mx-auto px-6 lg:px-10 pb-16">
  <div data-reveal class="mb-8">
    <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Board One</p>
    <h2 class="font-display text-3xl sm:text-4xl">Invest in Atlantis Homes</h2>
    <p class="text-slate mt-3 max-w-xl">A direct stake in the company itself — funding our pipeline across every development, rather than a single building.</p>
  </div>

  <div class="grid sm:grid-cols-2 gap-6">
    <?php foreach ($companyOpportunities as $opp): ?>
      <?= render_investment_card($opp, $user, $requestedInvestmentIds) ?>
    <?php endforeach; ?>
    <?php if (empty($companyOpportunities)): ?>
      <p class="text-slate sm:col-span-2">No company-wide offerings are open right now — check back soon.</p>
    <?php endif; ?>
  </div>
</section>

<?= render_skyline('text-gold/30 my-2') ?>

<!-- ================= INVESTMENT PROPERTIES ================= -->
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
  <div data-reveal class="mb-8">
    <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Board Two</p>
    <h2 class="font-display text-3xl sm:text-4xl">Investment Properties</h2>
    <p class="text-slate mt-3 max-w-xl">Standalone investment vehicles — land banking, rental-income shares, and similar plays. These are separate from the Portfolio, which is homes built for purchase.</p>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($propertyOpportunities as $opp): ?>
      <?= render_investment_card($opp, $user, $requestedInvestmentIds) ?>
    <?php endforeach; ?>
    <?php if (empty($propertyOpportunities)): ?>
      <p class="text-slate sm:col-span-3">No standalone investment properties are open right now — check back soon.</p>
    <?php endif; ?>
  </div>
</section>

<?php if (!$user): ?>
  <section class="max-w-3xl mx-auto px-6 lg:px-10 pb-24">
    <div data-reveal class="border border-white/10 rounded-3xl p-8 text-center bg-obsidian-card">
      <h2 class="font-display text-2xl mb-2">Sign in to invest</h2>
      <p class="text-slate text-sm mb-6">Create a free investor account to reserve a stake in either board above.</p>
      <div class="flex flex-wrap justify-center gap-3">
        <a href="<?= base_url('login.php') ?>" class="border border-white/15 hover:border-gold hover:text-gold font-semibold rounded-full px-7 py-3 transition-colors">Sign In</a>
        <a href="<?= base_url('register.php') ?>" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-7 py-3 transition-colors">Create an Account</a>
      </div>
    </div>
  </section>
<?php endif; ?>

<!-- ================= INVEST NOW MODAL (shared by every card above) ================= -->
<?php if ($user): ?>
<div id="invest-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div id="invest-modal-backdrop" class="absolute inset-0 bg-black/70"></div>
  <div class="relative glass border border-white/15 rounded-3xl max-w-lg w-full mx-auto p-8 max-h-[90vh] overflow-y-auto">
    <button id="close-invest-modal" type="button" aria-label="Close" class="absolute top-5 right-5 text-slate hover:text-white">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <h2 class="font-display text-2xl mb-1" id="invest-modal-title">Invest</h2>
    <p class="text-slate text-sm mb-6" id="invest-modal-subtitle"></p>

    <form data-inquiry-type="investment" id="invest-form" novalidate>
      <input type="hidden" name="message" id="invest-message-field">
      <input type="hidden" name="name" value="<?= h($user['name']) ?>">
      <input type="hidden" name="email" value="<?= h($user['email']) ?>">
      <input type="hidden" name="investment_id" id="invest-opportunity-id">

      <label class="block text-sm mb-5">
        <span class="block font-medium mb-1.5">Investment Amount (₦)</span>
        <input type="number" name="amount" id="invest-amount-input" required min="0" step="50000" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        <span class="block text-xs text-slate mt-1.5" id="invest-min-note"></span>
      </label>
      <label class="block text-sm mb-6">
        <span class="block font-medium mb-1.5">Phone</span>
        <input type="tel" name="phone" placeholder="+234" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      </label>

      <p data-form-error class="hidden text-sm text-red-400 mb-4" role="alert"></p>
      <p data-form-success class="hidden text-sm text-gold mb-4" role="status"></p>

      <button type="submit" class="w-full bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Reserve This Investment</button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php
function render_investment_card(array $opp, ?array $user, array $requestedInvestmentIds = []): string
{
    $pct = $opp['target_amount'] > 0 ? min(100, round(((float) $opp['amount_raised'] / (float) $opp['target_amount']) * 100)) : 0;
    $isOpen = $opp['status'] === 'open';
    ob_start();
    ?>
    <div class="border border-white/10 rounded-2xl overflow-hidden bg-obsidian-card">
      <div class="relative h-44 overflow-hidden">
        <img src="<?= h($opp['image_url']) ?>" alt="<?= h($opp['name']) ?>" class="w-full h-full object-cover">
        <span class="absolute top-4 left-4 text-xs font-semibold bg-obsidian/80 border border-gold/40 text-gold rounded-full px-3 py-1 backdrop-blur-sm"><?= $isOpen ? 'Open' : 'Fully Subscribed' ?></span>
      </div>
      <div class="p-6">
        <h3 class="font-display text-xl"><?= h($opp['name']) ?></h3>
        <?php if ($opp['location']): ?><p class="text-slate text-sm mt-1"><?= h($opp['location']) ?></p><?php endif; ?>
        <p class="text-slate text-sm mt-3 leading-relaxed line-clamp-3"><?= h($opp['description']) ?></p>

        <div class="mt-4">
          <div class="flex justify-between text-xs text-slate mb-1.5">
            <span><?= naira((float) $opp['amount_raised'], true) ?> raised</span>
            <span><?= $pct ?>% of <?= naira((float) $opp['target_amount'], true) ?></span>
          </div>
          <div class="h-1.5 rounded-full bg-white/5 overflow-hidden"><div class="h-full bg-gold" style="width: <?= $pct ?>%"></div></div>
        </div>

        <div class="mt-4 flex items-center justify-between text-sm">
          <span class="text-gold font-semibold"><?= (float) $opp['expected_roi_pct'] ?>% expected ROI</span>
          <span class="text-slate"><?= (int) $opp['term_months'] ?> month term</span>
        </div>
        <p class="text-xs text-slate mt-2">Minimum investment: <?= naira((float) $opp['min_investment']) ?></p>

        <?php
          $alreadyRequested = $user && in_array((int) $opp['id'], $requestedInvestmentIds, true);
        ?>
        <?php if (!$isOpen): ?>
          <button type="button" disabled class="mt-5 w-full text-sm font-semibold border border-white/10 text-slate rounded-full py-2.5 cursor-not-allowed">Fully Subscribed</button>
        <?php elseif ($alreadyRequested): ?>
          <button type="button" disabled class="mt-5 w-full text-sm font-semibold border border-gold/30 text-gold/70 rounded-full py-2.5 cursor-not-allowed">Request Submitted &mdash; We'll Be In Touch</button>
        <?php elseif ($user): ?>
          <button type="button" class="invest-now-btn mt-5 w-full bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-2.5 transition-colors"
            data-id="<?= (int) $opp['id'] ?>" data-name="<?= h($opp['name']) ?>" data-min="<?= (float) $opp['min_investment'] ?>"
            data-roi="<?= (float) $opp['expected_roi_pct'] ?>" data-term="<?= (int) $opp['term_months'] ?>">
            Invest Now
          </button>
        <?php else: ?>
          <a href="<?= base_url('login.php') ?>" class="mt-5 block text-center w-full border border-white/15 hover:border-gold hover:text-gold font-semibold rounded-full py-2.5 transition-colors">Sign In to Invest</a>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<script>
  // Server-computed average yield assumptions per development type,
  // handed to investor-hub.js so the math stays anchored to real listings.
  window.ATLANTIS_RATES = <?= json_encode($rateMap) ?>;
  window.ATLANTIS_PROPERTIES = <?= json_encode($allProperties) ?>;
</script>

<?php
$pageScripts = ['assets/js/investor-hub.js', 'assets/js/inquiry-form.js'];
require __DIR__ . '/includes/footer.php';
?>

<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';
$activeNav = 'home';

$featured = get_db()->query("SELECT * FROM properties WHERE featured = 1 ORDER BY id ASC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<!-- ================= HERO ================= -->
<section class="relative min-h-screen flex items-center overflow-hidden">
  <div class="absolute inset-0">
    <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1800&q=80"
         alt="" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-b from-obsidian/70 via-obsidian/60 to-obsidian"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-obsidian/80 via-transparent to-transparent"></div>
  </div>

  <div class="relative max-w-7xl mx-auto px-6 lg:px-10 pt-32 pb-20 w-full">
    <p data-reveal class="text-gold tracking-[0.25em] text-xs sm:text-sm uppercase font-semibold mb-6">Premium Construction &amp; Smart Real Estate Investment</p>
    <h1 data-reveal data-reveal-delay="100" class="font-display text-5xl sm:text-6xl lg:text-7xl leading-[1.05] max-w-4xl">
      Building Luxury That Lasts.<br><span class="text-gold italic">Creating Legacies.</span>
    </h1>
    <p data-reveal data-reveal-delay="200" class="mt-6 text-slate text-lg max-w-xl">
      From Ikoyi waterfront towers to Eko Atlantic skyline residences, Atlantis Homes designs, builds, and manages landmark properties — and the investments behind them.
    </p>
    <div data-reveal data-reveal-delay="300" class="mt-10 flex flex-wrap gap-4">
      <a href="/portfolio.php" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-8 py-4 transition-colors">View the Portfolio</a>
      <a href="/investor-hub.php" class="border border-white/20 hover:border-gold hover:text-gold font-semibold rounded-full px-8 py-4 transition-colors">Calculate Your ROI</a>
    </div>
  </div>

  <a href="#stats" aria-label="Scroll to stats" class="absolute bottom-8 left-1/2 -translate-x-1/2 text-gold animate-bounce">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 4v16M5 13l7 7 7-7"/></svg>
  </a>
</section>

<!-- ================= STAT COUNTERS ================= -->
<section id="stats" class="border-y border-white/10 bg-obsidian-soft bg-[#0E0E10]">
  <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16 grid grid-cols-1 sm:grid-cols-3 gap-12 text-center">
    <div data-reveal>
      <p class="font-display text-5xl text-gold"><span class="count-up" data-count-to="47" data-count-suffix="+">0</span></p>
      <p class="mt-2 text-slate uppercase text-xs tracking-[0.2em]">Properties Completed</p>
    </div>
    <div data-reveal data-reveal-delay="100">
      <p class="font-display text-5xl text-gold"><span class="count-up" data-count-to="12" data-count-suffix="+">0</span></p>
      <p class="mt-2 text-slate uppercase text-xs tracking-[0.2em]">Years of Experience</p>
    </div>
    <div data-reveal data-reveal-delay="200">
      <p class="font-display text-5xl text-gold"><span class="count-up" data-count-to="18.4" data-count-suffix="%">0</span></p>
      <p class="mt-2 text-slate uppercase text-xs tracking-[0.2em]">Avg. Investor ROI</p>
    </div>
  </div>
</section>

<!-- ================= FEATURED PROJECTS — horizontal scroll ================= -->
<section class="py-24">
  <div class="max-w-7xl mx-auto px-6 lg:px-10 flex items-end justify-between mb-10">
    <div data-reveal>
      <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Featured Projects</p>
      <h2 class="font-display text-3xl sm:text-4xl">Signature developments, currently open</h2>
    </div>
    <a href="/portfolio.php" class="hidden sm:inline-block text-sm font-semibold text-gold hover:text-gold-light whitespace-nowrap">View full portfolio &rarr;</a>
  </div>

  <div class="snap-rail flex gap-6 overflow-x-auto pb-6 px-6 lg:px-10 [&::-webkit-scrollbar]:h-1.5">
    <?php foreach ($featured as $p): ?>
      <a href="/portfolio.php#property-<?= (int) $p['id'] ?>"
         class="group snap-rail-item shrink-0 w-[300px] sm:w-[380px] rounded-2xl overflow-hidden border border-white/10 bg-obsidian-card hover:border-gold/50 transition-colors">
        <div class="relative h-56 overflow-hidden">
          <img src="<?= h($p['image_url']) ?>" alt="<?= h($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          <span class="absolute top-4 left-4 text-xs font-semibold bg-obsidian/80 border border-gold/40 text-gold rounded-full px-3 py-1 backdrop-blur-sm"><?= h(type_label($p['type'])) ?></span>
        </div>
        <div class="p-6">
          <h3 class="font-display text-xl"><?= h($p['name']) ?></h3>
          <p class="text-slate text-sm mt-1"><?= h($p['location']) ?></p>
          <div class="mt-4 flex items-center justify-between text-sm">
            <span class="text-gold font-semibold"><?= naira((float) $p['price_naira'], true) ?></span>
            <span class="text-slate"><?= (int) $p['roi_10yr_pct'] ?>% / 10yr</span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?= render_skyline('text-gold/30 my-2') ?>

<!-- ================= TRUST STRIP ================= -->
<section class="py-24">
  <div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-3 gap-10">
    <div data-reveal class="lg:col-span-1">
      <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Why Atlantis</p>
      <h2 class="font-display text-3xl sm:text-4xl leading-tight">Built on title-clean land, delivered on documented timelines.</h2>
    </div>
    <div class="lg:col-span-2 grid sm:grid-cols-2 gap-8">
      <div data-reveal data-reveal-delay="100" class="border-l-2 border-gold/40 pl-5">
        <h3 class="font-display text-lg text-gold mb-2">Transparent Construction</h3>
        <p class="text-slate text-sm leading-relaxed">Every active build reports its milestone stage and dated site photos directly to your investor dashboard.</p>
      </div>
      <div data-reveal data-reveal-delay="150" class="border-l-2 border-gold/40 pl-5">
        <h3 class="font-display text-lg text-gold mb-2">Clean Title, Always</h3>
        <p class="text-slate text-sm leading-relaxed">Our legal team verifies and documents land title before a single foundation is poured.</p>
      </div>
      <div data-reveal data-reveal-delay="200" class="border-l-2 border-gold/40 pl-5">
        <h3 class="font-display text-lg text-gold mb-2">Smart Investment Tools</h3>
        <p class="text-slate text-sm leading-relaxed">Model rental yield and capital appreciation on any unit before you commit, right from the Investor Hub.</p>
      </div>
      <div data-reveal data-reveal-delay="250" class="border-l-2 border-gold/40 pl-5">
        <h3 class="font-display text-lg text-gold mb-2">120+ Verified Reviews</h3>
        <p class="text-slate text-sm leading-relaxed">Read what existing owners and investors say, with verified-owner reviews flagged in gold.</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= CTA BANNER ================= -->
<section class="py-24">
  <div data-reveal class="max-w-5xl mx-auto px-6 lg:px-10 text-center border border-gold/30 rounded-3xl py-16 bg-gradient-to-b from-gold/10 to-transparent">
    <h2 class="font-display text-3xl sm:text-4xl">Ready to see your projected returns?</h2>
    <p class="text-slate mt-4 max-w-xl mx-auto">Run any of our active developments through the Smart Investment calculator and get a 5- and 10-year projection in seconds.</p>
    <a href="/investor-hub.php" class="inline-block mt-8 bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-8 py-4 transition-colors">Open the Investor Hub</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Portfolio';
$activeNav = 'portfolio';

// Server-rendered first paint — works without JS, and is what the AJAX
// filter below replaces once it runs.
$properties = get_db()->query("SELECT * FROM properties ORDER BY featured DESC, id ASC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="pt-36 pb-10 max-w-7xl mx-auto px-6 lg:px-10">
  <p data-reveal class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Properties Portfolio</p>
  <h1 data-reveal data-reveal-delay="100" class="font-display text-4xl sm:text-5xl max-w-2xl">Every development, at every stage of the build.</h1>

  <div class="mt-10 flex flex-wrap gap-3" id="filter-bar" role="group" aria-label="Filter properties by build stage">
    <button type="button" class="filter-btn active" data-type="all">All Properties</button>
    <button type="button" class="filter-btn" data-type="off-plan">Off-Plan</button>
    <button type="button" class="filter-btn" data-type="under-construction">Under Construction</button>
    <button type="button" class="filter-btn" data-type="completed">Completed</button>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 lg:px-10 pb-24">
  <p id="results-count" class="text-sm text-slate mb-6"><?= count($properties) ?> properties</p>

  <div id="properties-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7" aria-live="polite">
    <?php foreach ($properties as $p): ?>
      <?php
        $amenities = json_decode($p['amenities_json'], true) ?: [];
        echo render_property_card_php($p, $amenities);
      ?>
    <?php endforeach; ?>
  </div>

  <p id="empty-state" class="hidden text-center text-slate py-16">No properties match this filter yet — check back soon.</p>
</section>

<?php
/**
 * Server-side card renderer for the first paint (no-JS fallback).
 * Structurally mirrors renderCard() in portfolio.js so cards look
 * identical whether they came from PHP or from the AJAX response.
 */
function render_property_card_php(array $p, array $amenities): string
{
    $id = (int) $p['id'];
    ob_start();
    ?>
    <article id="property-<?= $id ?>" class="property-card border border-white/10 rounded-2xl overflow-hidden bg-obsidian-card hover:border-gold/40 transition-colors" data-id="<?= $id ?>" data-type="<?= h($p['type']) ?>">
      <div class="relative h-52 overflow-hidden">
        <img src="<?= h($p['image_url']) ?>" alt="<?= h($p['name']) ?>" class="w-full h-full object-cover">
        <span class="absolute top-4 left-4 text-xs font-semibold bg-obsidian/80 border border-gold/40 text-gold rounded-full px-3 py-1 backdrop-blur-sm"><?= h(type_label($p['type'])) ?></span>
      </div>
      <div class="p-6">
        <h3 class="font-display text-xl"><?= h($p['name']) ?></h3>
        <p class="text-slate text-sm mt-1"><?= h($p['location']) ?></p>
        <div class="mt-4 flex items-center gap-4 text-xs text-slate">
          <span><?= (int) $p['bedrooms'] ?> Beds</span>
          <span>&middot;</span>
          <span><?= (int) $p['bathrooms'] ?> Baths</span>
          <span>&middot;</span>
          <span><?= (int) $p['size_sqm'] ?> sqm</span>
        </div>
        <div class="mt-4 flex items-center justify-between">
          <span class="text-gold font-semibold"><?= naira((float) $p['price_naira'], true) ?></span>
          <span class="text-xs text-slate"><?= (int) $p['roi_10yr_pct'] ?>% proj. / 10yr</span>
        </div>
        <button type="button" class="view-details-btn mt-5 w-full text-sm font-semibold border border-white/15 hover:border-gold hover:text-gold rounded-full py-2.5 transition-colors" data-id="<?= $id ?>" aria-expanded="false">
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
          <div class="loading-spinner flex items-center gap-2 text-slate text-sm">
            <span class="pulse-dot">&#9679;</span> Loading details&hellip;
          </div>
        </div>
      </div>
    </article>
    <?php
    return ob_get_clean();
}

$pageScripts = ['/assets/js/portfolio.js'];
require __DIR__ . '/includes/footer.php';
?>


<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Reviews';
$activeNav = 'reviews';
$user = current_user();

$pdo = get_db();

$agg = $pdo->query("SELECT COUNT(*) AS n, AVG(rating) AS avg_rating FROM reviews WHERE status = 'approved'")->fetch();
$totalReviews = (int) $agg['n'];
$avgRating = $totalReviews > 0 ? round((float) $agg['avg_rating'], 1) : 0;

$breakdown = $pdo->query("SELECT rating, COUNT(*) AS n FROM reviews WHERE status = 'approved' GROUP BY rating")->fetchAll();
$breakdownMap = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($breakdown as $row) {
    $breakdownMap[(int) $row['rating']] = (int) $row['n'];
}

$reviews = $pdo->query("
    SELECT r.*, u.name AS user_name
    FROM reviews r LEFT JOIN users u ON u.id = r.user_id
    WHERE r.status = 'approved'
    ORDER BY r.created_at DESC
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="pt-36 pb-12 max-w-7xl mx-auto px-6 lg:px-10">
  <div class="grid lg:grid-cols-3 gap-10 items-start">
    <div data-reveal class="lg:col-span-2">
      <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Reviews &amp; Testimonials</p>
      <h1 class="font-display text-4xl sm:text-5xl">What owners and investors are saying.</h1>
      <p class="mt-4 text-slate max-w-xl">Every review below is screened by our team before publishing. Verified owners — confirmed buyers in our system — are marked in gold.</p>
      <button id="open-review-modal" type="button" class="mt-8 bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-7 py-3.5 transition-colors">Write a Review</button>
    </div>

    <div data-reveal data-reveal-delay="100" class="border border-white/10 rounded-3xl p-8 bg-obsidian-card">
      <div class="flex items-baseline gap-3">
        <span class="font-display text-5xl text-gold"><?= number_format($avgRating, 1) ?></span>
        <span class="text-slate text-sm">out of 5</span>
      </div>
      <p class="text-slate text-sm mt-1">Based on <?= $totalReviews ?> reviews</p>
      <div class="mt-5 space-y-2">
        <?php for ($star = 5; $star >= 1; $star--): ?>
          <?php $pct = $totalReviews > 0 ? round(($breakdownMap[$star] / $totalReviews) * 100) : 0; ?>
          <div class="flex items-center gap-3 text-xs text-slate">
            <span class="w-8"><?= $star ?>&#9733;</span>
            <span class="flex-1 h-1.5 rounded-full bg-white/10 overflow-hidden"><span class="block h-full bg-gold" style="width: <?= $pct ?>%"></span></span>
            <span class="w-8 text-right"><?= $breakdownMap[$star] ?></span>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 lg:px-10 pb-24">
  <div id="reviews-feed" class="review-masonry">
    <?php foreach ($reviews as $r): ?>
      <?= render_review_card($r) ?>
    <?php endforeach; ?>
  </div>
  <?php if (empty($reviews)): ?>
    <p class="text-center text-slate py-16">Be the first to leave a review.</p>
  <?php endif; ?>
</section>

<!-- ================= WRITE A REVIEW MODAL ================= -->
<div id="review-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div id="review-modal-backdrop" class="absolute inset-0 bg-black/70"></div>
  <div class="relative glass border border-white/15 rounded-3xl max-w-lg w-full mx-auto p-8 max-h-[90vh] overflow-y-auto">
    <button id="close-review-modal" type="button" aria-label="Close" class="absolute top-5 right-5 text-slate hover:text-white">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    <h2 class="font-display text-2xl mb-1">Write a Review</h2>
    <p class="text-slate text-sm mb-6">Reviews are checked by our team before they appear publicly — usually within one business day.</p>

    <form id="review-form" novalidate>
      <div class="mb-5">
        <span class="block text-sm font-medium mb-2">Your Rating</span>
        <div id="star-rating" role="radiogroup" aria-label="Rating out of 5">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <button type="button" class="star-btn" data-value="<?= $i ?>" role="radio" aria-checked="false" aria-label="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">&#9733;</button>
          <?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="rating-input" value="">
      </div>

      <?php if ($user): ?>
        <p class="text-sm bg-white/5 border border-white/10 rounded-xl px-4 py-3 mb-5">
          Posting as <span class="text-gold font-medium"><?= h($user['name']) ?></span> (<?= h($user['email']) ?>)
        </p>
      <?php else: ?>
        <div class="grid sm:grid-cols-2 gap-4 mb-5">
          <label class="block text-sm">
            <span class="block font-medium mb-1.5">Full Name</span>
            <input type="text" name="guest_name" required class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
          </label>
          <label class="block text-sm">
            <span class="block font-medium mb-1.5">Email</span>
            <input type="email" name="guest_email" required class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
          </label>
        </div>
      <?php endif; ?>

      <label class="block text-sm mb-5">
        <span class="block font-medium mb-1.5">Review Title</span>
        <input type="text" name="title" required maxlength="120" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      </label>

      <label class="block text-sm mb-5">
        <span class="block font-medium mb-1.5">Your Review</span>
        <textarea name="body" required minlength="20" maxlength="1000" rows="4" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none resize-none"></textarea>
      </label>

      <!-- Mock spam-mitigation widget — visual stand-in for a real reCAPTCHA/Turnstile embed -->
      <label class="flex items-center gap-3 mb-6 border border-white/15 rounded-xl px-4 py-3 bg-white/5 cursor-pointer text-sm">
        <input type="checkbox" name="captcha" required class="w-4 h-4 accent-[#C5A880]">
        <span>I'm not a robot</span>
        <span class="ml-auto text-[10px] text-slate uppercase tracking-wider">Atlantis Verify</span>
      </label>

      <p id="form-error" class="hidden text-sm text-red-400 mb-4" role="alert"></p>
      <p id="form-success" class="hidden text-sm text-gold mb-4" role="status"></p>

      <button type="submit" id="review-submit-btn" class="w-full bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Submit Review</button>
    </form>
  </div>
</div>

<?php
function render_review_card(array $r): string
{
    $name = $r['user_id'] ? $r['user_name'] : $r['guest_name'];
    $initial = strtoupper(substr($name, 0, 1));
    ob_start();
    ?>
    <article class="border border-white/10 rounded-2xl p-6 bg-obsidian-card">
      <div class="flex items-center gap-3 mb-4">
        <span class="w-10 h-10 rounded-full bg-gold/15 text-gold font-display flex items-center justify-center text-sm"><?= h($initial) ?></span>
        <div>
          <p class="font-medium text-sm flex items-center gap-2">
            <?= h($name) ?>
            <?php if ($r['verified_owner']): ?>
              <span class="text-[10px] uppercase tracking-wide font-semibold bg-gold/15 text-gold border border-gold/30 rounded-full px-2 py-0.5">Verified Owner</span>
            <?php endif; ?>
          </p>
          <p class="text-xs text-slate"><?= h(time_ago($r['created_at'])) ?></p>
        </div>
      </div>
      <p class="text-gold text-sm mb-2"><?= str_repeat('★', (int) $r['rating']) ?><span class="text-white/15"><?= str_repeat('★', 5 - (int) $r['rating']) ?></span></p>
      <h3 class="font-display text-lg mb-2"><?= h($r['title']) ?></h3>
      <p class="text-slate text-sm leading-relaxed"><?= h($r['body']) ?></p>
    </article>
    <?php
    return ob_get_clean();
}

$pageScripts = ['/assets/js/reviews.js'];
require __DIR__ . '/includes/footer.php';
?>

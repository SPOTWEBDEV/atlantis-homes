<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Analytics';
$activeAdminNav = 'analytics';

$pdo = get_db();

$totalProperties = (int) $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$portfolioValue = (float) $pdo->query("SELECT SUM(price_naira) FROM properties")->fetchColumn();
$totalInvestors = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$pendingReviews = (int) $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
$avgRating = (float) ($pdo->query("SELECT AVG(rating) FROM reviews WHERE status = 'approved'")->fetchColumn() ?: 0);
$totalCollected = (float) $pdo->query("SELECT SUM(amount_paid) FROM purchases")->fetchColumn();
$totalContracted = (float) $pdo->query("SELECT SUM(total_price) FROM purchases")->fetchColumn();

$byType = $pdo->query("SELECT type, COUNT(*) AS n FROM properties GROUP BY type")->fetchAll();
$recentReviews = $pdo->query("
    SELECT r.*, u.name AS user_name FROM reviews r LEFT JOIN users u ON u.id = r.user_id
    ORDER BY r.created_at DESC LIMIT 5
")->fetchAll();

require __DIR__ . '/../includes/admin-header.php';
?>

<p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
<h1 class="font-display text-3xl sm:text-4xl mb-10">Analytics Overview</h1>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-12">
  <div class="border border-white/10 rounded-2xl p-6 bg-obsidian-card">
    <p class="text-xs text-slate uppercase tracking-wider">Total Properties</p>
    <p class="font-display text-3xl text-gold mt-2"><?= $totalProperties ?></p>
  </div>
  <div class="border border-white/10 rounded-2xl p-6 bg-obsidian-card">
    <p class="text-xs text-slate uppercase tracking-wider">Portfolio Value</p>
    <p class="font-display text-3xl text-gold mt-2"><?= naira($portfolioValue, true) ?></p>
  </div>
  <div class="border border-white/10 rounded-2xl p-6 bg-obsidian-card">
    <p class="text-xs text-slate uppercase tracking-wider">Registered Investors</p>
    <p class="font-display text-3xl text-gold mt-2"><?= $totalInvestors ?></p>
  </div>
  <div class="border border-white/10 rounded-2xl p-6 bg-obsidian-card relative">
    <p class="text-xs text-slate uppercase tracking-wider">Pending Reviews</p>
    <p class="font-display text-3xl text-gold mt-2"><?= $pendingReviews ?></p>
    <?php if ($pendingReviews > 0): ?>
      <a href="<?= base_url('admin/reviews.php') ?>" class="absolute top-5 right-5 text-xs font-semibold bg-gold/15 text-gold rounded-full px-2.5 py-1">Review &rarr;</a>
    <?php endif; ?>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 border border-white/10 rounded-2xl p-6 bg-obsidian-card">
    <h2 class="font-display text-xl mb-5">Collections vs. Contracted Value</h2>
    <?php $collectedPct = $totalContracted > 0 ? round(($totalCollected / $totalContracted) * 100) : 0; ?>
    <div class="flex justify-between text-sm mb-2">
      <span class="text-slate">Collected: <span class="text-white font-medium"><?= naira($totalCollected) ?></span></span>
      <span class="text-slate">Contracted: <span class="text-white font-medium"><?= naira($totalContracted) ?></span></span>
    </div>
    <div class="h-3 rounded-full bg-white/5 overflow-hidden">
      <div class="h-full bg-gold" style="width: <?= $collectedPct ?>%"></div>
    </div>
    <p class="text-xs text-slate mt-2"><?= $collectedPct ?>% of contracted purchase value collected to date.</p>

    <h2 class="font-display text-xl mt-10 mb-5">Listings by Build Stage</h2>
    <div class="space-y-3">
      <?php foreach ($byType as $row): ?>
        <?php $pct = $totalProperties > 0 ? round(($row['n'] / $totalProperties) * 100) : 0; ?>
        <div>
          <div class="flex justify-between text-xs text-slate mb-1.5">
            <span class="text-white font-medium"><?= h(type_label($row['type'])) ?></span>
            <span><?= $row['n'] ?> listings</span>
          </div>
          <div class="h-2 rounded-full bg-white/5 overflow-hidden"><div class="h-full bg-gold" style="width: <?= $pct ?>%"></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="border border-white/10 rounded-2xl p-6 bg-obsidian-card">
    <h2 class="font-display text-xl mb-1">Average Rating</h2>
    <p class="font-display text-4xl text-gold mt-3"><?= number_format($avgRating, 1) ?> <span class="text-base text-slate">/ 5</span></p>
    <div class="mt-6 pt-6 border-t border-white/10">
      <h3 class="text-sm font-medium mb-3">Recent Reviews</h3>
      <ul class="space-y-3">
        <?php foreach ($recentReviews as $r): ?>
          <li class="text-sm">
            <span class="text-gold"><?= str_repeat('★', (int) $r['rating']) ?></span>
            <span class="text-white"><?= h($r['title']) ?></span>
            <span class="text-xs text-slate ml-1">— <?= h($r['status']) ?></span>
          </li>
        <?php endforeach; ?>
        <?php if (empty($recentReviews)): ?><li class="text-sm text-slate">No reviews yet.</li><?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>

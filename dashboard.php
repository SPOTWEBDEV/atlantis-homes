<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login(base_url('login.php'));

$pageTitle = 'My Portal';
$activeNav = 'dashboard';
$user = current_user();

$pdo = get_db();
$stmt = $pdo->prepare("
    SELECT pu.*, pr.name AS property_name, pr.location, pr.image_url, pr.milestone_stage
    FROM purchases pu
    JOIN properties pr ON pr.id = pu.property_id
    WHERE pu.user_id = ?
    ORDER BY pu.created_at DESC
");
$stmt->execute([$user['id']]);
$purchases = $stmt->fetchAll();

$paymentsByPurchase = [];
if ($purchases) {
    $ids = array_column($purchases, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $payStmt = $pdo->prepare("SELECT * FROM payments WHERE purchase_id IN ($placeholders) ORDER BY paid_on ASC");
    $payStmt->execute($ids);
    foreach ($payStmt->fetchAll() as $row) {
        $paymentsByPurchase[$row['purchase_id']][] = $row;
    }
}

$stages = ['Foundation', 'Framing', 'Roofing', 'Finishing', 'Completed'];

require __DIR__ . '/includes/header.php';
?>

<section class="pt-36 pb-12 max-w-6xl mx-auto px-6 lg:px-10">
  <p data-reveal class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">My Portal</p>
  <h1 data-reveal data-reveal-delay="100" class="font-display text-4xl sm:text-5xl">Welcome back, <?= h(explode(' ', $user['name'])[0]) ?>.</h1>
</section>

<section class="max-w-6xl mx-auto px-6 lg:px-10 pb-24">
  <?php if (empty($purchases)): ?>
    <div data-reveal class="border border-white/10 rounded-3xl p-12 text-center bg-obsidian-card">
      <p class="text-slate">You don't have an active purchase on file yet.</p>
      <a href="<?= base_url('portfolio.php') ?>" class="inline-block mt-6 bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-7 py-3 transition-colors">Browse the Portfolio</a>
    </div>
  <?php endif; ?>

  <?php foreach ($purchases as $purchase): ?>
    <?php
      $stageIndex = milestone_index($purchase['milestone_stage']);
      $fillPct = count($stages) > 1 ? ($stageIndex / (count($stages) - 1)) * 100 : 0;
      $outstanding = max(0, (float) $purchase['total_price'] - (float) $purchase['amount_paid']);
      $paidPct = $purchase['total_price'] > 0 ? min(100, round(((float) $purchase['amount_paid'] / (float) $purchase['total_price']) * 100)) : 0;
      $payments = $paymentsByPurchase[$purchase['id']] ?? [];
    ?>
    <div data-reveal class="border border-white/10 rounded-3xl overflow-hidden bg-obsidian-card mb-10">
      <div class="grid lg:grid-cols-3">
        <div class="h-56 lg:h-auto">
          <img src="<?= h($purchase['image_url']) ?>" alt="<?= h($purchase['property_name']) ?>" class="w-full h-full object-cover">
        </div>
        <div class="lg:col-span-2 p-8">
          <h2 class="font-display text-2xl"><?= h($purchase['property_name']) ?></h2>
          <p class="text-slate text-sm mt-1"><?= h($purchase['location']) ?></p>

          <!-- Milestone progress tracker -->
          <div class="mt-8">
            <p class="text-sm font-medium mb-5">Construction Milestone</p>
            <div class="milestone-track flex justify-between">
              <div class="milestone-fill" style="width: <?= $fillPct ?>%"></div>
              <?php foreach ($stages as $i => $stage): ?>
                <div class="relative z-10 flex flex-col items-center" style="width: <?= 100 / count($stages) ?>%">
                  <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold <?= $i <= $stageIndex ? 'bg-gold text-obsidian' : 'bg-[#2A2A2D] text-slate' ?>">
                    <?= $i < $stageIndex ? '&#10003;' : $i + 1 ?>
                  </span>
                  <span class="mt-2 text-[11px] text-center <?= $i <= $stageIndex ? 'text-gold' : 'text-slate' ?>"><?= h($stage) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Financial ledger -->
          <div class="mt-10 grid sm:grid-cols-3 gap-4">
            <div class="border border-white/10 rounded-xl p-4">
              <p class="text-xs text-slate">Total Price</p>
              <p class="font-display text-lg text-gold mt-1"><?= naira((float) $purchase['total_price']) ?></p>
            </div>
            <div class="border border-white/10 rounded-xl p-4">
              <p class="text-xs text-slate">Paid (<?= $paidPct ?>%)</p>
              <p class="font-display text-lg mt-1"><?= naira((float) $purchase['amount_paid']) ?></p>
            </div>
            <div class="border border-white/10 rounded-xl p-4">
              <p class="text-xs text-slate">Outstanding</p>
              <p class="font-display text-lg mt-1"><?= naira($outstanding) ?></p>
            </div>
          </div>

          <?php if ($payments): ?>
            <div class="mt-8">
              <p class="text-sm font-medium mb-3">Payment History</p>
              <div class="border border-white/10 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                  <thead class="bg-white/5 text-slate text-xs uppercase tracking-wider">
                    <tr><th class="text-left px-4 py-2.5">Date</th><th class="text-left px-4 py-2.5">Description</th><th class="text-right px-4 py-2.5">Amount</th></tr>
                  </thead>
                  <tbody class="divide-y divide-white/5">
                    <?php foreach ($payments as $pay): ?>
                      <tr>
                        <td class="px-4 py-2.5 text-slate"><?= h(date('d M Y', strtotime($pay['paid_on']))) ?></td>
                        <td class="px-4 py-2.5"><?= h($pay['label']) ?></td>
                        <td class="px-4 py-2.5 text-right text-gold font-medium"><?= naira((float) $pay['amount']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

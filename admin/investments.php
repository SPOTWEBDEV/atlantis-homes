<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Investment Opportunities';
$activeAdminNav = 'investments';

$opportunities = get_db()->query("SELECT * FROM investment_opportunities ORDER BY type ASC, created_at DESC")->fetchAll();

require __DIR__ . '/../includes/admin-header.php';
?>

<div class="flex items-center justify-between mb-4">
  <div>
    <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
    <h1 class="font-display text-3xl sm:text-4xl">Investment Opportunities</h1>
  </div>
  <a href="<?= base_url('admin/investment-form.php') ?>" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-6 py-3 text-sm transition-colors">+ Add Opportunity</a>
</div>
<p class="text-slate text-sm mb-10 max-w-2xl">These are separate from the home-buying Portfolio — investing here means a stake in the company or a standalone investment property, not purchasing a home.</p>

<div class="border border-white/10 rounded-2xl overflow-hidden bg-obsidian-card">
  <table class="w-full text-sm" id="investments-table">
    <thead class="bg-white/5 text-slate text-xs uppercase tracking-wider">
      <tr>
        <th class="text-left px-5 py-3.5">Name</th>
        <th class="text-left px-5 py-3.5">Board</th>
        <th class="text-left px-5 py-3.5">Raised / Target</th>
        <th class="text-left px-5 py-3.5">Status</th>
        <th class="text-right px-5 py-3.5">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-white/5">
      <?php foreach ($opportunities as $inv): ?>
        <?php $pct = $inv['target_amount'] > 0 ? min(100, round(((float) $inv['amount_raised'] / (float) $inv['target_amount']) * 100)) : 0; ?>
        <tr data-id="<?= (int) $inv['id'] ?>" data-name="<?= h($inv['name']) ?>">
          <td class="px-5 py-4">
            <p class="font-medium"><?= h($inv['name']) ?></p>
            <?php if ($inv['location']): ?><p class="text-xs text-slate"><?= h($inv['location']) ?></p><?php endif; ?>
          </td>
          <td class="px-5 py-4 text-slate"><?= $inv['type'] === 'company' ? 'Invest in Atlantis Homes' : 'Investment Property' ?></td>
          <td class="px-5 py-4 text-gold font-medium"><?= naira((float) $inv['amount_raised'], true) ?> / <?= naira((float) $inv['target_amount'], true) ?> <span class="text-slate text-xs">(<?= $pct ?>%)</span></td>
          <td class="px-5 py-4">
            <span class="text-xs font-semibold rounded-full px-3 py-1 <?= $inv['status'] === 'open' ? 'bg-gold/15 text-gold' : 'bg-white/10 text-slate' ?>"><?= h(ucfirst($inv['status'])) ?></span>
          </td>
          <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="<?= base_url('admin/investment-form.php') ?>?id=<?= (int) $inv['id'] ?>" class="text-xs font-semibold text-slate hover:text-white mr-3">Edit</a>
            <button type="button" class="delete-investment-btn text-xs font-semibold text-red-400 hover:text-red-300">Delete</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (empty($opportunities)): ?>
    <p class="text-slate text-center py-12">No investment opportunities yet.</p>
  <?php endif; ?>
</div>

<?php
$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>

<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Investor Ledger';
$activeAdminNav = 'purchases';

$pdo = get_db();

$purchases = $pdo->query("
    SELECT pu.*, u.name AS investor_name, u.email AS investor_email, p.name AS property_name
    FROM purchases pu
    JOIN users u ON u.id = pu.user_id
    JOIN properties p ON p.id = pu.property_id
    ORDER BY pu.created_at DESC
")->fetchAll();

$investors = $pdo->query("SELECT id, name, email FROM users WHERE role = 'client' ORDER BY name ASC")->fetchAll();
$properties = $pdo->query("SELECT id, name, price_naira FROM properties ORDER BY name ASC")->fetchAll();

require __DIR__ . '/../includes/admin-header.php';
?>

<div class="flex items-center justify-between mb-10">
  <div>
    <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
    <h1 class="font-display text-3xl sm:text-4xl">Investor Ledger</h1>
  </div>
  <button type="button" id="open-purchase-modal" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-6 py-3 text-sm transition-colors">+ Assign a Purchase</button>
</div>

<div id="purchases-list" class="space-y-5">
  <?php foreach ($purchases as $purchase): ?>
    <?= render_purchase_card($purchase) ?>
  <?php endforeach; ?>
  <?php if (empty($purchases)): ?>
    <p class="text-slate text-center py-12 border border-white/10 rounded-2xl bg-obsidian-card">No purchases on file yet. Assign one to get started.</p>
  <?php endif; ?>
</div>

<!-- ================= ASSIGN A NEW PURCHASE MODAL ================= -->
<div id="purchase-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div id="purchase-modal-backdrop" class="absolute inset-0 bg-black/70"></div>
  <div class="relative glass border border-white/15 rounded-3xl max-w-lg w-full mx-auto p-8 max-h-[90vh] overflow-y-auto">
    <button id="close-purchase-modal" type="button" aria-label="Close" class="absolute top-5 right-5 text-slate hover:text-white">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <h2 class="font-display text-2xl mb-6">Assign a Purchase</h2>

    <form id="create-purchase-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <label class="block text-sm mb-5">
        <span class="block font-medium mb-1.5">Investor</span>
        <select name="user_id" required class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
          <option value="">Select an investor&hellip;</option>
          <?php foreach ($investors as $inv): ?>
            <option value="<?= (int) $inv['id'] ?>"><?= h($inv['name']) ?> (<?= h($inv['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="block text-sm mb-5">
        <span class="block font-medium mb-1.5">Property</span>
        <select name="property_id" id="purchase-property-select" required class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
          <option value="">Select a property&hellip;</option>
          <?php foreach ($properties as $p): ?>
            <option value="<?= (int) $p['id'] ?>" data-price="<?= (float) $p['price_naira'] ?>"><?= h($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="grid sm:grid-cols-2 gap-5 mb-5">
        <label class="block text-sm">
          <span class="block font-medium mb-1.5">Total Contract Price (₦)</span>
          <input type="number" name="total_price" id="purchase-total-price" required min="0" step="100000" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        </label>
        <label class="block text-sm">
          <span class="block font-medium mb-1.5">Initial Deposit (₦)</span>
          <input type="number" name="initial_payment" min="0" step="50000" value="0" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        </label>
      </div>
      <p id="purchase-form-error" class="hidden text-sm text-red-400 mb-4" role="alert"></p>
      <p id="purchase-form-success" class="hidden text-sm text-gold mb-4" role="status"></p>
      <button type="submit" class="w-full bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Create Purchase</button>
    </form>
  </div>
</div>

<?php
function render_purchase_card(array $purchase): string
{
    $outstanding = max(0, (float) $purchase['total_price'] - (float) $purchase['amount_paid']);
    $paidPct = $purchase['total_price'] > 0 ? min(100, round(((float) $purchase['amount_paid'] / (float) $purchase['total_price']) * 100)) : 0;
    ob_start();
    ?>
    <div class="purchase-card border border-white/10 rounded-2xl p-6 bg-obsidian-card" data-purchase-id="<?= (int) $purchase['id'] ?>">
      <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
        <div>
          <p class="font-medium"><?= h($purchase['investor_name']) ?> <span class="text-slate text-xs">(<?= h($purchase['investor_email']) ?>)</span></p>
          <p class="text-sm text-gold"><?= h($purchase['property_name']) ?></p>
        </div>
        <button type="button" class="add-payment-btn text-xs font-semibold bg-gold/15 text-gold hover:bg-gold hover:text-obsidian rounded-full px-3.5 py-1.5 transition-colors">+ Add Payment</button>
      </div>

      <div class="grid sm:grid-cols-3 gap-4 text-sm mb-4">
        <div><p class="text-xs text-slate">Total Price</p><p class="font-medium"><?= naira((float) $purchase['total_price']) ?></p></div>
        <div><p class="text-xs text-slate">Paid (<?= $paidPct ?>%)</p><p class="font-medium text-gold paid-amount"><?= naira((float) $purchase['amount_paid']) ?></p></div>
        <div><p class="text-xs text-slate">Outstanding</p><p class="font-medium outstanding-amount"><?= naira($outstanding) ?></p></div>
      </div>
      <div class="h-1.5 rounded-full bg-white/5 overflow-hidden">
        <div class="h-full bg-gold payment-progress-bar" style="width: <?= $paidPct ?>%"></div>
      </div>

      <!-- Inline "Add Payment" form, hidden until the button above is clicked -->
      <form class="add-payment-form hidden mt-5 pt-5 border-t border-white/10 grid sm:grid-cols-3 gap-3" novalidate>
        <input type="hidden" name="purchase_id" value="<?= (int) $purchase['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="number" name="amount" required min="0" step="10000" placeholder="Amount (₦)" class="bg-white/5 border border-white/15 focus:border-gold rounded-xl px-3 py-2 text-sm outline-none">
        <input type="text" name="label" placeholder="e.g. Installment 4 of 5" class="bg-white/5 border border-white/15 focus:border-gold rounded-xl px-3 py-2 text-sm outline-none">
        <button type="submit" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-xl text-sm transition-colors">Record Payment</button>
      </form>
      <p class="payment-form-error hidden text-xs text-red-400 mt-2" role="alert"></p>
    </div>
    <?php
    return ob_get_clean();
}

$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>

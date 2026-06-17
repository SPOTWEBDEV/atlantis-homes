<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Investment Opportunity Form';
$activeAdminNav = 'investments';

$editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$inv = null;
if ($editId) {
    $stmt = get_db()->prepare('SELECT * FROM investment_opportunities WHERE id = ?');
    $stmt->execute([$editId]);
    $inv = $stmt->fetch();
}

require __DIR__ . '/../includes/admin-header.php';
?>

<a href="<?= base_url('admin/investments.php') ?>" class="text-sm text-slate hover:text-white mb-6 inline-block">&larr; Back to Investment Opportunities</a>

<p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
<h1 class="font-display text-3xl sm:text-4xl mb-10"><?= $inv ? 'Edit Opportunity' : 'Add an Investment Opportunity' ?></h1>

<form id="investment-form" class="border border-white/10 rounded-2xl p-8 bg-obsidian-card max-w-3xl" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="id" value="<?= $inv ? (int) $inv['id'] : '' ?>">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
  <input type="hidden" name="existing_image_url" value="<?= $inv ? h($inv['image_url']) : '' ?>">

  <fieldset class="mb-6">
    <legend class="text-sm font-medium mb-3">Board</legend>
    <div class="grid grid-cols-2 gap-2">
      <label class="type-pill flex items-center justify-center gap-2 cursor-pointer <?= (!$inv || $inv['type'] === 'company') ? 'active' : '' ?>">
        <input type="radio" name="type" value="company" class="hidden" <?= (!$inv || $inv['type'] === 'company') ? 'checked' : '' ?>> Invest in Atlantis Homes
      </label>
      <label class="type-pill flex items-center justify-center gap-2 cursor-pointer <?= ($inv && $inv['type'] === 'property') ? 'active' : '' ?>">
        <input type="radio" name="type" value="property" class="hidden" <?= ($inv && $inv['type'] === 'property') ? 'checked' : '' ?>> Investment Property
      </label>
    </div>
  </fieldset>

  <div class="grid sm:grid-cols-2 gap-5 mb-5">
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Name</span>
      <input type="text" name="name" required value="<?= $inv ? h($inv['name']) : '' ?>" placeholder="e.g. Atlantis Homes Growth Fund — Series II" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Location <span class="text-slate">(for property board, optional)</span></span>
      <input type="text" name="location" value="<?= $inv ? h($inv['location']) : '' ?>" placeholder="e.g. Epe, Lagos" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
  </div>

  <label class="block text-sm mb-5">
    <span class="block font-medium mb-1.5">Description</span>
    <textarea name="description" rows="4" required class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none resize-none"><?= $inv ? h($inv['description']) : '' ?></textarea>
  </label>

  <div class="grid sm:grid-cols-2 gap-5 mb-5">
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Minimum Investment (₦)</span>
      <input type="number" name="min_investment" required min="0" step="50000" value="<?= $inv ? (float) $inv['min_investment'] : 2000000 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Expected ROI (%)</span>
      <input type="number" name="expected_roi_pct" required min="0" step="0.1" value="<?= $inv ? (float) $inv['expected_roi_pct'] : 20 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
  </div>

  <div class="grid sm:grid-cols-3 gap-5 mb-5">
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Target Amount (₦)</span>
      <input type="number" name="target_amount" required min="0" step="1000000" value="<?= $inv ? (float) $inv['target_amount'] : 100000000 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Amount Raised So Far (₦)</span>
      <input type="number" name="amount_raised" min="0" step="100000" value="<?= $inv ? (float) $inv['amount_raised'] : 0 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Term (months)</span>
      <input type="number" name="term_months" required min="1" value="<?= $inv ? (int) $inv['term_months'] : 36 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
  </div>

  <label class="block text-sm mb-5">
    <span class="block font-medium mb-1.5">Status</span>
    <select name="status" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      <option value="open" <?= (!$inv || $inv['status'] === 'open') ? 'selected' : '' ?>>Open — accepting investment</option>
      <option value="closed" <?= ($inv && $inv['status'] === 'closed') ? 'selected' : '' ?>>Closed — fully subscribed</option>
    </select>
  </label>

  <label class="block text-sm mb-6">
    <span class="block font-medium mb-1.5">Cover Image</span>
    <?php if ($inv && $inv['image_url']): ?>
      <img src="<?= h($inv['image_url']) ?>" alt="Current cover image" class="w-full h-32 object-cover rounded-lg border border-white/10 mb-2">
    <?php endif; ?>
    <input type="file" name="image" accept="image/png,image/jpeg,image/webp" <?= $inv ? '' : 'required' ?> class="w-full text-sm text-slate file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gold file:text-obsidian file:font-semibold file:text-sm">
    <span class="block text-xs text-slate mt-1.5"><?= $inv ? 'Leave empty to keep the current image.' : 'JPEG, PNG, or WebP — up to 5MB.' ?></span>
  </label>

  <p id="investment-form-error" class="hidden text-sm text-red-400 mb-4" role="alert"></p>
  <p id="investment-form-success" class="hidden text-sm text-gold mb-4" role="status"></p>

  <button type="submit" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-7 py-3 transition-colors"><?= $inv ? 'Save Changes' : 'Add Opportunity' ?></button>
</form>

<?php
$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>

<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Property Management';
$activeAdminNav = 'properties';

$properties = get_db()->query("SELECT * FROM properties ORDER BY id ASC")->fetchAll();

require __DIR__ . '/../includes/admin-header.php';
?>

<div class="flex items-center justify-between mb-10">
  <div>
    <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
    <h1 class="font-display text-3xl sm:text-4xl">Property Management</h1>
  </div>
  <a href="<?= base_url('admin/property-form.php') ?>" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-6 py-3 text-sm transition-colors">+ Add Property</a>
</div>

<div class="border border-white/10 rounded-2xl overflow-hidden bg-obsidian-card">
  <table class="w-full text-sm" id="properties-table">
    <thead class="bg-white/5 text-slate text-xs uppercase tracking-wider">
      <tr>
        <th class="text-left px-5 py-3.5">Property</th>
        <th class="text-left px-5 py-3.5">Type</th>
        <th class="text-left px-5 py-3.5">Stage</th>
        <th class="text-right px-5 py-3.5">Price</th>
        <th class="text-right px-5 py-3.5">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-white/5">
      <?php foreach ($properties as $p): ?>
        <tr data-id="<?= (int) $p['id'] ?>" data-name="<?= h($p['name']) ?>">
          <td class="px-5 py-4">
            <p class="font-medium"><?= h($p['name']) ?></p>
            <p class="text-xs text-slate"><?= h($p['location']) ?></p>
          </td>
          <td class="px-5 py-4 text-slate"><?= h(type_label($p['type'])) ?></td>
          <td class="px-5 py-4">
            <span class="text-xs font-semibold bg-gold/15 text-gold rounded-full px-3 py-1"><?= h($p['milestone_stage']) ?></span>
          </td>
          <td class="px-5 py-4 text-right text-gold font-medium"><?= naira((float) $p['price_naira'], true) ?></td>
          <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="<?= base_url('admin/update-property.php') ?>?id=<?= (int) $p['id'] ?>" class="text-xs font-semibold text-gold hover:text-gold-light mr-3">Milestone</a>
            <a href="<?= base_url('admin/property-form.php') ?>?id=<?= (int) $p['id'] ?>" class="text-xs font-semibold text-slate hover:text-white mr-3">Edit</a>
            <button type="button" class="delete-property-btn text-xs font-semibold text-red-400 hover:text-red-300">Delete</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php
$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>

<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Property Milestone Updater';
$activeAdminNav = 'properties';

$pdo = get_db();
$properties = $pdo->query("SELECT id, name, location, milestone_stage FROM properties ORDER BY name ASC")->fetchAll();

if (empty($properties)) {
    $selected = null;
} else {
    $requestedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $selected = null;
    foreach ($properties as $p) {
        if ((int) $p['id'] === $requestedId) { $selected = $p; break; }
    }
    if (!$selected) {
        $selected = $properties[0];
    }
}

$stages = ['Foundation', 'Framing', 'Roofing', 'Finishing', 'Completed'];

$recentUpdates = [];
if ($selected) {
    $stmt = $pdo->prepare("
        SELECT pu.*, u.name AS admin_name FROM property_updates pu
        LEFT JOIN users u ON u.id = pu.admin_id
        WHERE pu.property_id = ? ORDER BY pu.created_at DESC LIMIT 8
    ");
    $stmt->execute([$selected['id']]);
    $recentUpdates = $stmt->fetchAll();
}

require __DIR__ . '/../includes/admin-header.php';
?>

<p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
<h1 class="font-display text-3xl sm:text-4xl mb-10">Property Milestone Updater</h1>

<?php if (!$selected): ?>
  <p class="text-slate">No properties found — add some to the database first.</p>
<?php else: ?>

<div class="grid lg:grid-cols-5 gap-8">
  <form id="update-form" class="lg:col-span-3 border border-white/10 rounded-2xl p-7 bg-obsidian-card" enctype="multipart/form-data">
    <label class="block text-sm mb-5">
      <span class="block font-medium mb-1.5">Select Development</span>
      <select id="property-select" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        <?php foreach ($properties as $p): ?>
          <option value="<?= (int) $p['id'] ?>" <?= $p['id'] === $selected['id'] ? 'selected' : '' ?>><?= h($p['name']) ?> — <?= h($p['location']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <input type="hidden" name="property_id" id="property-id-input" value="<?= (int) $selected['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

    <p class="text-sm mb-5">Current stage: <span id="current-stage-badge" class="text-xs font-semibold bg-gold/15 text-gold rounded-full px-3 py-1"><?= h($selected['milestone_stage']) ?></span></p>

    <label class="block text-sm mb-5">
      <span class="block font-medium mb-1.5">New Milestone Stage</span>
      <select name="milestone" id="milestone-select" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        <?php foreach ($stages as $stage): ?>
          <option value="<?= h($stage) ?>" <?= $stage === $selected['milestone_stage'] ? 'selected' : '' ?>><?= h($stage) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="block text-sm mb-5">
      <span class="block font-medium mb-1.5">Update Note</span>
      <textarea name="note" rows="4" required minlength="5" maxlength="600" placeholder="e.g. Roofing complete on Blocks A & B; Block C scaffolding up this week." class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none resize-none"></textarea>
    </label>

    <label class="block text-sm mb-6">
      <span class="block font-medium mb-1.5">Site Photos</span>
      <input type="file" name="photos[]" id="photos-input" accept="image/png,image/jpeg,image/webp" multiple class="w-full text-sm text-slate file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gold file:text-obsidian file:font-semibold file:text-sm">
      <span class="block text-xs text-slate mt-1.5">JPEG, PNG or WebP — up to 5MB each. (Files are stored locally as a demo; swap in cloud storage for production.)</span>
    </label>

    <p id="update-error" class="hidden text-sm text-red-400 mb-4" role="alert"></p>
    <p id="update-success" class="hidden text-sm text-gold mb-4" role="status"></p>

    <button type="submit" id="update-submit-btn" class="w-full bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Post Update</button>
  </form>

  <div class="lg:col-span-2 border border-white/10 rounded-2xl p-7 bg-obsidian-card">
    <h2 class="font-display text-xl mb-5">Recent Updates</h2>
    <ul id="updates-log" class="space-y-5">
      <?php foreach ($recentUpdates as $u): ?>
        <?= render_update_entry($u) ?>
      <?php endforeach; ?>
    </ul>
    <p id="updates-empty" class="<?= $recentUpdates ? 'hidden' : '' ?> text-sm text-slate">No updates logged for this property yet.</p>
  </div>
</div>

<?php endif; ?>

<?php
function render_update_entry(array $u): string
{
    $photos = array_filter(explode(',', $u['photo_path']));
    ob_start();
    ?>
    <li class="border-b border-white/5 pb-5 last:border-0 last:pb-0">
      <div class="flex items-center justify-between text-xs text-slate mb-1.5">
        <span class="font-semibold text-gold"><?= h($u['milestone']) ?></span>
        <span><?= h(time_ago($u['created_at'])) ?></span>
      </div>
      <p class="text-sm"><?= h($u['note']) ?></p>
      <?php if ($photos): ?>
        <p class="text-xs text-slate mt-1.5"><?= count($photos) ?> photo<?= count($photos) === 1 ? '' : 's' ?> attached</p>
      <?php endif; ?>
      <p class="text-xs text-slate mt-1">— <?= h($u['admin_name'] ?? 'Admin') ?></p>
    </li>
    <?php
    return ob_get_clean();
}

$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>

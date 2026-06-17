<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Property Form';
$activeAdminNav = 'properties';

$editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$property = null;
if ($editId) {
    $stmt = get_db()->prepare('SELECT * FROM properties WHERE id = ?');
    $stmt->execute([$editId]);
    $property = $stmt->fetch();
}

$types = ['off-plan' => 'Off-Plan', 'under-construction' => 'Under Construction', 'completed' => 'Completed'];
$stages = ['Foundation', 'Framing', 'Roofing', 'Finishing', 'Completed'];
$amenitiesText = $property ? implode(', ', json_decode($property['amenities_json'], true) ?: []) : '';

require __DIR__ . '/../includes/admin-header.php';
?>

<a href="<?= base_url('admin/properties.php') ?>" class="text-sm text-slate hover:text-white mb-6 inline-block">&larr; Back to Property Management</a>

<p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
<h1 class="font-display text-3xl sm:text-4xl mb-10"><?= $property ? 'Edit Property' : 'Add a New Property' ?></h1>

<form id="property-form" class="border border-white/10 rounded-2xl p-8 bg-obsidian-card max-w-3xl" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="id" value="<?= $property ? (int) $property['id'] : '' ?>">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
  <input type="hidden" name="existing_image_url" value="<?= $property ? h($property['image_url']) : '' ?>">
  <input type="hidden" name="existing_floor_plan_url" value="<?= $property ? h($property['floor_plan_url']) : '' ?>">

  <div class="grid sm:grid-cols-2 gap-5 mb-5">
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Property Name</span>
      <input type="text" name="name" required value="<?= $property ? h($property['name']) : '' ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Location</span>
      <input type="text" name="location" required value="<?= $property ? h($property['location']) : '' ?>" placeholder="e.g. Ikoyi, Lagos" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
  </div>

  <div class="grid sm:grid-cols-3 gap-5 mb-5">
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Type</span>
      <select name="type" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        <?php foreach ($types as $val => $label): ?>
          <option value="<?= h($val) ?>" <?= ($property && $property['type'] === $val) ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Price (₦)</span>
      <input type="number" name="price_naira" required min="0" step="100000" value="<?= $property ? (float) $property['price_naira'] : '' ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Starting Milestone</span>
      <select name="milestone_stage" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        <?php foreach ($stages as $stage): ?>
          <option value="<?= h($stage) ?>" <?= ($property && $property['milestone_stage'] === $stage) ? 'selected' : '' ?>><?= h($stage) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>

  <div class="grid sm:grid-cols-4 gap-5 mb-5">
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Bedrooms</span>
      <input type="number" name="bedrooms" min="0" value="<?= $property ? (int) $property['bedrooms'] : 3 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Bathrooms</span>
      <input type="number" name="bathrooms" min="0" value="<?= $property ? (int) $property['bathrooms'] : 3 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Size (sqm)</span>
      <input type="number" name="size_sqm" min="0" value="<?= $property ? (int) $property['size_sqm'] : 150 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm flex items-end pb-2.5">
      <span class="flex items-center gap-2 text-sm font-medium">
        <input type="checkbox" name="featured" value="1" <?= ($property && $property['featured']) ? 'checked' : '' ?> class="w-4 h-4 accent-[#C5A880]">
        Featured on homepage
      </span>
    </label>
  </div>

  <div class="grid sm:grid-cols-2 gap-5 mb-5">
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">5-Year Projected ROI (%)</span>
      <input type="number" name="roi_5yr_pct" min="0" step="0.1" value="<?= $property ? (float) $property['roi_5yr_pct'] : 35 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">10-Year Projected ROI (%)</span>
      <input type="number" name="roi_10yr_pct" min="0" step="0.1" value="<?= $property ? (float) $property['roi_10yr_pct'] : 85 ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
    </label>
  </div>

  <label class="block text-sm mb-5">
    <span class="block font-medium mb-1.5">Short Summary <span class="text-slate">(shown on cards, under 300 characters)</span></span>
    <textarea name="summary" rows="2" required maxlength="300" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none resize-none"><?= $property ? h($property['summary']) : '' ?></textarea>
  </label>

  <label class="block text-sm mb-5">
    <span class="block font-medium mb-1.5">Full Overview</span>
    <textarea name="overview" rows="4" required class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none resize-none"><?= $property ? h($property['overview']) : '' ?></textarea>
  </label>

  <label class="block text-sm mb-5">
    <span class="block font-medium mb-1.5">Amenities <span class="text-slate">(comma-separated)</span></span>
    <input type="text" name="amenities" value="<?= h($amenitiesText) ?>" placeholder="Rooftop pool, 24/7 security, Smart-home automation" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
  </label>

  <div class="grid sm:grid-cols-2 gap-5 mb-6">
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Cover Image <?= $property ? '' : '' ?></span>
      <?php if ($property && $property['image_url']): ?>
        <img src="<?= h($property['image_url']) ?>" alt="Current cover image" class="w-full h-32 object-cover rounded-lg border border-white/10 mb-2">
      <?php endif; ?>
      <input type="file" name="image" accept="image/png,image/jpeg,image/webp" <?= $property ? '' : 'required' ?> class="w-full text-sm text-slate file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gold file:text-obsidian file:font-semibold file:text-sm">
      <span class="block text-xs text-slate mt-1.5"><?= $property ? 'Leave empty to keep the current image.' : 'JPEG, PNG, or WebP — up to 5MB.' ?></span>
    </label>
    <label class="block text-sm">
      <span class="block font-medium mb-1.5">Floor Plan Image</span>
      <?php if ($property && $property['floor_plan_url']): ?>
        <img src="<?= h($property['floor_plan_url']) ?>" alt="Current floor plan" class="w-full h-32 object-cover rounded-lg border border-white/10 mb-2">
      <?php endif; ?>
      <input type="file" name="floor_plan" accept="image/png,image/jpeg,image/webp" class="w-full text-sm text-slate file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gold file:text-obsidian file:font-semibold file:text-sm">
      <span class="block text-xs text-slate mt-1.5"><?= $property ? 'Leave empty to keep the current floor plan.' : 'Optional — JPEG, PNG, or WebP.' ?></span>
    </label>
  </div>

  <p id="form-error" class="hidden text-sm text-red-400 mb-4" role="alert"></p>
  <p id="form-success" class="hidden text-sm text-gold mb-4" role="status"></p>

  <button type="submit" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-7 py-3 transition-colors"><?= $property ? 'Save Changes' : 'Add Property' ?></button>
</form>

<?php
$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>

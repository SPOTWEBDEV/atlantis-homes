<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Book a Session';
$activeNav = 'book';
$user = current_user();

$properties = get_db()->query("SELECT id, name, location FROM properties ORDER BY name ASC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="pt-36 pb-20 max-w-2xl mx-auto px-6">
  <div data-reveal class="text-center mb-10">
    <p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Book a Session</p>
    <h1 class="font-display text-4xl sm:text-5xl">Tour a property, or talk strategy.</h1>
    <p class="text-slate mt-4">Site visits, virtual walkthroughs, or a one-on-one investment consultation — tell us what you'd like and we'll confirm a time.</p>
  </div>

  <form data-reveal data-reveal-delay="100" data-inquiry-type="booking" class="border border-white/10 rounded-3xl p-8 bg-obsidian-card" novalidate>
    <div class="grid sm:grid-cols-2 gap-5 mb-5">
      <label class="block text-sm">
        <span class="block font-medium mb-1.5">Full Name</span>
        <input type="text" name="name" required value="<?= $user ? h($user['name']) : '' ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      </label>
      <label class="block text-sm">
        <span class="block font-medium mb-1.5">Email</span>
        <input type="email" name="email" required value="<?= $user ? h($user['email']) : '' ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      </label>
    </div>

    <div class="grid sm:grid-cols-2 gap-5 mb-5">
      <label class="block text-sm">
        <span class="block font-medium mb-1.5">Phone</span>
        <input type="tel" name="phone" placeholder="+234" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      </label>
      <label class="block text-sm">
        <span class="block font-medium mb-1.5">Preferred Date</span>
        <input type="date" name="preferred_date" min="<?= date('Y-m-d') ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
      </label>
    </div>

    <label class="block text-sm mb-5">
      <span class="block font-medium mb-1.5">Property of Interest <span class="text-slate">(optional)</span></span>
      <select name="property_id" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none">
        <option value="">General consultation — not tied to a specific property</option>
        <?php foreach ($properties as $p): ?>
          <option value="<?= (int) $p['id'] ?>"><?= h($p['name']) ?> — <?= h($p['location']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="block text-sm mb-6">
      <span class="block font-medium mb-1.5">What would you like to discuss?</span>
      <textarea name="message" rows="4" required minlength="10" maxlength="2000" placeholder="e.g. I'd like an in-person tour of the Eko Atlantic units, ideally a Saturday morning." class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-4 py-2.5 outline-none resize-none"></textarea>
    </label>

    <p data-form-error class="hidden text-sm text-red-400 mb-4" role="alert"></p>
    <p data-form-success class="hidden text-sm text-gold mb-4" role="status"></p>

    <button type="submit" class="w-full bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full py-3.5 transition-colors">Request a Session</button>
  </form>
</section>

<?php
$pageScripts = ['assets/js/inquiry-form.js'];
require __DIR__ . '/includes/footer.php';
?>

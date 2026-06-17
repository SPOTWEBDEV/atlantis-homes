<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Inquiries';
$activeAdminNav = 'inquiries';

$inquiries = get_db()->query("
    SELECT i.*, p.name AS property_name
    FROM inquiries i LEFT JOIN properties p ON p.id = i.property_id
    ORDER BY i.created_at DESC
")->fetchAll();

$typeBadge = [
    'booking' => 'bg-gold/15 text-gold',
    'contact' => 'bg-white/10 text-white',
    'estimate' => 'bg-blue-500/15 text-blue-300',
    'investment' => 'bg-green-500/15 text-green-300',
];
$typeLabel = ['booking' => 'Booking', 'contact' => 'Contact', 'estimate' => 'Estimate Quote', 'investment' => 'Investment Interest'];

require __DIR__ . '/../includes/admin-header.php';
?>

<p class="text-gold tracking-[0.25em] text-xs uppercase font-semibold mb-3">Control Center</p>
<h1 class="font-display text-3xl sm:text-4xl mb-2">Inquiries</h1>
<p class="text-slate text-sm mb-10">Bookings, contact messages, and quote/investment requests from the public site.</p>

<div class="space-y-4" id="inquiries-list">
  <?php foreach ($inquiries as $inq): ?>
    <div class="inquiry-card border border-white/10 rounded-2xl p-6 bg-obsidian-card" data-id="<?= (int) $inq['id'] ?>">
      <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
        <div class="flex items-center gap-3">
          <span class="text-[11px] font-semibold uppercase tracking-wide rounded-full px-2.5 py-1 <?= $typeBadge[$inq['type']] ?? 'bg-white/10 text-white' ?>"><?= h($typeLabel[$inq['type']] ?? $inq['type']) ?></span>
          <p class="font-medium"><?= h($inq['name']) ?></p>
          <span class="status-badge text-[11px] font-semibold uppercase tracking-wide rounded-full px-2.5 py-1 <?= $inq['status'] === 'contacted' ? 'bg-green-500/15 text-green-300' : 'bg-white/10 text-slate' ?>"><?= h($inq['status']) ?></span>
        </div>
        <button type="button" class="toggle-status-btn text-xs font-semibold text-gold hover:text-gold-light"><?= $inq['status'] === 'contacted' ? 'Mark as New' : 'Mark Contacted' ?></button>
      </div>
      <div class="text-xs text-slate flex flex-wrap gap-x-5 gap-y-1 mb-3">
        <span><?= h($inq['email']) ?></span>
        <?php if ($inq['phone']): ?><span><?= h($inq['phone']) ?></span><?php endif; ?>
        <?php if ($inq['property_name']): ?><span class="text-gold"><?= h($inq['property_name']) ?></span><?php endif; ?>
        <?php if ($inq['preferred_date']): ?><span>Preferred: <?= h($inq['preferred_date']) ?></span><?php endif; ?>
        <span><?= h(time_ago($inq['created_at'])) ?></span>
      </div>
      <p class="text-sm whitespace-pre-line"><?= h($inq['message']) ?></p>
    </div>
  <?php endforeach; ?>
  <?php if (empty($inquiries)): ?>
    <p class="text-slate text-center py-12 border border-white/10 rounded-2xl bg-obsidian-card">No inquiries yet.</p>
  <?php endif; ?>
</div>

<?php
$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>

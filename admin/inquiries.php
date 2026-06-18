<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin(base_url('login.php'));

$pageTitle = 'Inquiries';
$activeAdminNav = 'inquiries';

// --- Filters (plain GET params, so the page is bookmarkable/shareable) ---
$filterType = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';
$filterQ = trim((string) ($_GET['q'] ?? ''));

$allowedTypes = ['booking', 'contact', 'estimate', 'investment'];
$allowedStatuses = ['new', 'contacted'];

$where = [];
$params = [];

if (in_array($filterType, $allowedTypes, true)) {
    $where[] = 'i.type = :type';
    $params[':type'] = $filterType;
}
if (in_array($filterStatus, $allowedStatuses, true)) {
    $where[] = 'i.status = :status';
    $params[':status'] = $filterStatus;
}
if ($filterDateFrom !== '') {
    $where[] = "date(i.created_at) >= :date_from";
    $params[':date_from'] = $filterDateFrom;
}
if ($filterDateTo !== '') {
    $where[] = "date(i.created_at) <= :date_to";
    $params[':date_to'] = $filterDateTo;
}
if ($filterQ !== '') {
    $where[] = '(i.name LIKE :q OR i.email LIKE :q)';
    $params[':q'] = '%' . $filterQ . '%';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$pdo = get_db();
$stmt = $pdo->prepare("
    SELECT i.*, p.name AS property_name, inv.name AS investment_name
    FROM inquiries i
    LEFT JOIN properties p ON p.id = i.property_id
    LEFT JOIN investment_opportunities inv ON inv.id = i.investment_id
    $whereSql
    ORDER BY i.created_at DESC
");
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

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
<p class="text-slate text-sm mb-8">Bookings, contact messages, and quote/investment requests from the public site.</p>

<form method="get" class="border border-white/10 rounded-2xl p-5 bg-obsidian-card mb-8 grid sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
  <label class="block text-sm">
    <span class="block text-xs text-slate mb-1.5">Type</span>
    <select name="type" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-3 py-2 outline-none">
      <option value="">All Types</option>
      <?php foreach ($typeLabel as $val => $label): ?>
        <option value="<?= h($val) ?>" <?= $filterType === $val ? 'selected' : '' ?>><?= h($label) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="block text-sm">
    <span class="block text-xs text-slate mb-1.5">Status</span>
    <select name="status" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-3 py-2 outline-none">
      <option value="">All Statuses</option>
      <option value="new" <?= $filterStatus === 'new' ? 'selected' : '' ?>>New</option>
      <option value="contacted" <?= $filterStatus === 'contacted' ? 'selected' : '' ?>>Contacted</option>
    </select>
  </label>
  <label class="block text-sm">
    <span class="block text-xs text-slate mb-1.5">From Date</span>
    <input type="date" name="date_from" value="<?= h($filterDateFrom) ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-3 py-2 outline-none">
  </label>
  <label class="block text-sm">
    <span class="block text-xs text-slate mb-1.5">To Date</span>
    <input type="date" name="date_to" value="<?= h($filterDateTo) ?>" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-3 py-2 outline-none">
  </label>
  <label class="block text-sm">
    <span class="block text-xs text-slate mb-1.5">Search Name/Email</span>
    <input type="text" name="q" value="<?= h($filterQ) ?>" placeholder="e.g. funmi" class="w-full bg-white/5 border border-white/15 focus:border-gold rounded-xl px-3 py-2 outline-none">
  </label>
  <div class="sm:col-span-2 lg:col-span-5 flex gap-3">
    <button type="submit" class="bg-gold hover:bg-gold-light text-obsidian font-semibold rounded-full px-6 py-2.5 text-sm transition-colors">Apply Filters</button>
    <a href="<?= base_url('admin/inquiries.php') ?>" class="text-sm font-semibold text-slate hover:text-white px-2 py-2.5">Clear</a>
  </div>
</form>

<p class="text-slate text-sm mb-4"><?= count($inquiries) ?> result<?= count($inquiries) === 1 ? '' : 's' ?></p>

<div class="space-y-4" id="inquiries-list">
  <?php foreach ($inquiries as $inq): ?>
    <?php
      $specDetails = [];
      if ($inq['spec_details']) {
          $specDetails = json_decode($inq['spec_details'], true) ?: [];
      }
    ?>
    <div class="inquiry-card border border-white/10 rounded-2xl p-6 bg-obsidian-card" data-id="<?= (int) $inq['id'] ?>">
      <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
        <div class="flex items-center gap-3 flex-wrap">
          <span class="text-[11px] font-semibold uppercase tracking-wide rounded-full px-2.5 py-1 <?= $typeBadge[$inq['type']] ?? 'bg-white/10 text-white' ?>"><?= h($typeLabel[$inq['type']] ?? $inq['type']) ?></span>
          <p class="font-medium"><?= h($inq['name']) ?></p>
          <span class="status-badge text-[11px] font-semibold uppercase tracking-wide rounded-full px-2.5 py-1 <?= $inq['status'] === 'contacted' ? 'bg-green-500/15 text-green-300' : 'bg-white/10 text-slate' ?>"><?= h($inq['status']) ?></span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <a href="mailto:<?= h($inq['email']) ?>" class="text-xs font-semibold border border-white/15 hover:border-gold hover:text-gold rounded-full px-3 py-1.5 transition-colors">Email</a>
          <?php if ($inq['phone']): ?>
            <a href="tel:<?= h(preg_replace('/\s+/', '', $inq['phone'])) ?>" class="text-xs font-semibold border border-white/15 hover:border-gold hover:text-gold rounded-full px-3 py-1.5 transition-colors">Call</a>
          <?php endif; ?>
          <button type="button" class="toggle-status-btn text-xs font-semibold bg-gold/15 text-gold hover:bg-gold hover:text-obsidian rounded-full px-3 py-1.5 transition-colors"><?= $inq['status'] === 'contacted' ? 'Mark as New' : 'Mark Contacted' ?></button>
        </div>
      </div>
      <div class="text-xs text-slate flex flex-wrap gap-x-5 gap-y-1 mb-3">
        <span><?= h($inq['email']) ?></span>
        <?php if ($inq['phone']): ?><span><?= h($inq['phone']) ?></span><?php endif; ?>
        <?php if ($inq['property_name']): ?><span class="text-gold"><?= h($inq['property_name']) ?></span><?php endif; ?>
        <?php if ($inq['investment_name']): ?><span class="text-gold"><?= h($inq['investment_name']) ?></span><?php endif; ?>
        <?php if ($inq['preferred_date']): ?><span>Preferred: <?= h($inq['preferred_date']) ?></span><?php endif; ?>
        <span><?= h(date('d M Y, H:i', strtotime($inq['created_at']))) ?></span>
      </div>
      <p class="text-sm whitespace-pre-line"><?= h($inq['message']) ?></p>

      <?php if ($specDetails): ?>
        <div class="mt-4 pt-4 border-t border-white/10 flex flex-wrap gap-2">
          <?php foreach ($specDetails as $key => $value): ?>
            <span class="amenity-pill"><?= h(ucwords(str_replace('_', ' ', (string) $key))) ?>: <?= h((string) $value) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if (empty($inquiries)): ?>
    <p class="text-slate text-center py-12 border border-white/10 rounded-2xl bg-obsidian-card">No inquiries match these filters.</p>
  <?php endif; ?>
</div>

<?php
$pageScripts = ['assets/js/admin.js'];
require __DIR__ . '/../includes/admin-footer.php';
?>

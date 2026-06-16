<?php
/** Shorthand for htmlspecialchars — use around every piece of user/DB text echoed into HTML. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function naira(float $amount, bool $compact = false): string
{
    if ($compact) {
        if ($amount >= 1_000_000_000) {
            return '₦' . rtrim(rtrim(number_format($amount / 1_000_000_000, 1), '0'), '.') . 'B';
        }
        if ($amount >= 1_000_000) {
            return '₦' . rtrim(rtrim(number_format($amount / 1_000_000, 1), '0'), '.') . 'M';
        }
    }
    return '₦' . number_format($amount);
}

function type_label(string $type): string
{
    return match ($type) {
        'off-plan' => 'Off-Plan',
        'under-construction' => 'Under Construction',
        'completed' => 'Completed',
        default => ucfirst($type),
    };
}

function milestone_index(string $stage): int
{
    $stages = ['Foundation', 'Framing', 'Roofing', 'Finishing', 'Completed'];
    $i = array_search($stage, $stages, true);
    return $i === false ? 0 : $i;
}

/**
 * Renders the brand's signature skyline silhouette — a thin gold line-art
 * divider used between sections, in the footer, and as a loading shimmer.
 * Kept as one shared function so the motif stays exactly consistent
 * everywhere it appears.
 */
function render_skyline(string $extraClass = ''): string
{
    $class = trim('skyline-divider ' . $extraClass);
    return '<svg class="' . h($class) . '" viewBox="0 0 1200 80" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
        . '<path d="M0 80 L0 50 L40 50 L40 30 L70 30 L70 50 L110 50 L110 10 L150 10 L150 50 L190 50 L190 38 L230 38 L230 50 '
        . 'L270 50 L270 5 L300 5 L300 22 L330 22 L330 50 L380 50 L380 18 L400 18 L400 50 L440 50 L440 0 L470 0 L470 50 '
        . 'L520 50 L520 32 L555 32 L555 50 L600 50 L600 14 L630 14 L630 50 L670 50 L670 42 L700 42 L700 50 L740 50 '
        . 'L740 8 L770 8 L770 50 L820 50 L820 24 L850 24 L850 50 L900 50 L900 0 L930 0 L930 50 L970 50 L970 36 L1000 36 '
        . 'L1000 50 L1040 50 L1040 12 L1070 12 L1070 50 L1110 50 L1110 28 L1150 28 L1150 50 L1200 50 L1200 80 Z" fill="currentColor"/>'
        . '</svg>';
}

function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M Y', strtotime($datetime));
}

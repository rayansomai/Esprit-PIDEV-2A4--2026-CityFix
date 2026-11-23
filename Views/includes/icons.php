<?php
/**
 * Views/includes/icons.php
 * Icônes SVG intégrées (style linéaire, trait 1,8 px), sans bibliothèque externe.
 * Utilisation dans une vue : <?= icon('users') ?>
 * Les icônes sont décoratives (aria-hidden) : le texte ou un aria-label porte le sens.
 */

declare(strict_types=1);

if (!function_exists('icon')) {
    function icon(string $name, string $class = 'cf-icon'): string
    {
        static $paths = [
            'logo'     => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><path d="M9.5 10l2 2 3.5-3.5"/>',
            'pin'      => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
            'wrench'   => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/>',
            'file'     => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
            'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0M16 4.5a3.5 3.5 0 0 1 0 7M18 13.5a6 6 0 0 1 4 6.5"/>',
            'chart'    => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
            'shield'   => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/>',
            'shield-check' => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
            'bulb'     => '<path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5c.7.7 1 1.5 1 2.5h6c0-1 .3-1.8 1-2.5A6 6 0 0 0 12 3z"/>',
            'search'   => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
            'logout'   => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/>',
            'grid'     => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
            'key'      => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M17 6l3 3"/>',
            'edit'     => '<path d="M4 20h4L19 9l-4-4L4 16z"/>',
            'eye'      => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'ban'      => '<circle cx="12" cy="12" r="9"/><path d="M6 6l12 12"/>',
            'check'    => '<path d="M5 12l5 5 9-10"/>',
            'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
            'plus'     => '<path d="M12 5v14M5 12h14"/>',
            'trash'    => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>',
            'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
            'menu'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'x'        => '<path d="M6 6l12 12M18 6L6 18"/>',
            'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
            'arrow-up' => '<path d="M12 19V5M6 11l6-6 6 6"/>',
            'alert'    => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18h.01"/>',
            'lock'     => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
            'home'     => '<path d="M3 11l9-8 9 8M5 9.5V21h14V9.5"/>',
            'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5h.01"/>',
            'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        ];
        $svg = $paths[$name] ?? $paths['info'];
        return '<svg class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $svg . '</svg>';
    }
}

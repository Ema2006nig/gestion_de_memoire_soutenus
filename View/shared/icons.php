<?php
/**
 * Petite bibliothèque d'icônes SVG (style Lucide / Heroicons outline).
 * Aucun emoji, aucune dépendance externe. Une seule fonction : icon($nom, $classes).
 *
 *   <?= icon('home') ?>
 *   <?= icon('users', 'w-5 h-5 text-emerald-600') ?>
 */

function icon(string $name, string $class = 'w-5 h-5'): string
{
    static $paths = null;

    if ($paths === null) {
        $paths = [
            'home'         => '<path d="M3 12 12 3l9 9"/><path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"/>',
            'library'      => '<path d="M16 6 4 6"/><path d="M16 12 4 12"/><path d="M16 18 4 18"/><path d="M20 4v16"/>',
            'book-open'    => '<path d="M12 7c-1.7-1.5-4-2-7-2v14c3 0 5.3.5 7 2"/><path d="M12 7c1.7-1.5 4-2 7-2v14c-3 0-5.3.5-7 2"/><path d="M12 7v14"/>',
            'bell'         => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/>',
            'users'        => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'user'         => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'cap'          => '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5a8 8 0 0 0 12 0v-5"/>',
            'check'        => '<path d="m5 12 5 5L20 7"/>',
            'check-double' => '<path d="m2 12 4 4 8-8"/><path d="m10 12 4 4 8-8"/>',
            'eye'          => '<path d="M2 12s4-8 10-8 10 8 10 8-4 8-10 8S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'upload'       => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/>',
            'send'         => '<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
            'log-out'      => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
            'lock'         => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'mail'         => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
            'menu'         => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'x'            => '<path d="M18 6 6 18M6 6l12 12"/>',
            'search'       => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'plus'         => '<path d="M12 5v14M5 12h14"/>',
            'edit'         => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="m18 2 4 4-11 11H7v-4z"/>',
            'trash'        => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>',
            'file-text'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/>',
            'calendar'     => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'map-pin'      => '<path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
            'heart'        => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>',
            'message'      => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
            'alert'        => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
            'info'         => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
            'arrow-left'   => '<path d="m12 19-7-7 7-7M19 12H5"/>',
            'arrow-right'  => '<path d="m12 5 7 7-7 7M5 12h14"/>',
            'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
            'settings'     => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
            'clipboard'    => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>',
            'shield'       => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'sparkles'     => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/>',
            'paperclip'    => '<path d="m21 12-8.5 8.5a5.5 5.5 0 1 1-7.8-7.8L13.2 4.2a3.7 3.7 0 1 1 5.2 5.2L9.8 18a1.8 1.8 0 1 1-2.6-2.6l7.8-7.8"/>',
            'circle'       => '<circle cx="12" cy="12" r="9"/>',
            'thumbs-up'    => '<path d="M7 22V11"/><path d="M15 5.88 14 12h5.5a2 2 0 0 1 2 2.26l-1.1 7A2 2 0 0 1 18.42 23H7"/><path d="M7 11H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h3"/>',
        ];
    }

    $body = $paths[$name] ?? $paths['circle'];

    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" '
         . 'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" '
         . 'class="' . htmlspecialchars($class) . '" aria-hidden="true">'
         . $body
         . '</svg>';
}
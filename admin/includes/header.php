<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>ASA Admin</title>

    <link rel="stylesheet" href="/assets/css/tailwind.css" />
    <link rel="stylesheet" href="/assets/css/custom.css" />
</head>

<?php
$showAdminNav = $showAdminNav ?? true;
$bodyClass = $bodyClass ?? 'admin-body';
$mainClass = $mainClass ?? 'mx-auto max-w-7xl px-6 py-10';

$adminNavLinks = [
    [
        'label' => 'Overzicht',
        'href' => '/admin/index.php',
        'class' => 'btn btn-secondary',
    ],
    [
        'label' => 'Producten',
        'href' => '/admin/products/',
        'class' => 'btn btn-secondary',
    ],
    [
        'label' => 'Bestellingen',
        'href' => '/admin/orders/',
        'class' => 'btn btn-secondary',
    ],
];

$adminNavActions = [
    [
        'label' => '+ Nieuw product',
        'href' => '/admin/products/create.php',
        'class' => 'btn btn-primary',
    ],
    [
        'label' => 'Log uit',
        'href' => '/admin/auth/logout.php',
        'class' => 'btn btn-danger',
    ],
];
?>

<body class="<?= h($bodyClass) ?>">
<div class="fixed inset-0 -z-50 pointer-events-none">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 h-[520px] w-[520px] rounded-full blur-[90px] bg-gold-400/10"></div>
    <div class="absolute top-40 -left-24 h-[520px] w-[520px] rounded-full blur-[100px] bg-gold-300/8"></div>
</div>

<header class="sticky top-0 z-50 bg-ink/70 backdrop-blur-xl border-b border-white/5">
    <div class="mx-auto max-w-7xl px-6 py-4 flex items-center justify-between gap-4">
        <a href="/admin/index.php" class="group flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl luxe-ring shadow-glow bg-black/30 flex items-center justify-center">
                <span class="font-display gold-text">A</span>
            </div>
            <div class="leading-none">
                <div class="font-display tracking-[.22em] text-sm gold-text">ASA</div>
                <div class="text-[11px] tracking-[.34em] text-white/60 -mt-1">ADMIN</div>
            </div>
        </a>

        <?php if ($showAdminNav) : ?>
            <div class="flex items-center gap-3">
                <nav class="hidden md:flex items-center gap-3 text-sm">
                    <?php foreach ($adminNavLinks as $link) : ?>
                        <a class="<?= h($link['class']) ?>" href="<?= h($link['href']) ?>">
                            <?= h($link['label']) ?>
                        </a>
                    <?php endforeach; ?>
                    <?php foreach ($adminNavActions as $link) : ?>
                        <a class="<?= h($link['class']) ?>" href="<?= h($link['href']) ?>">
                            <?= h($link['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <button
                    type="button"
                    id="adminMenuButton"
                    class="md:hidden inline-flex items-center justify-center h-11 w-11 rounded-2xl luxe-ring btn-secondary transition"
                    aria-controls="adminMobileMenu"
                    aria-expanded="false"
                >
                    <span class="sr-only">Open menu</span>
                    <svg class="h-5 w-5 text-white/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="6" x2="20" y2="6" />
                        <line x1="4" y1="12" x2="20" y2="12" />
                        <line x1="4" y1="18" x2="20" y2="18" />
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($showAdminNav) : ?>
        <div id="adminMobileMenu" class="md:hidden hidden border-t border-white/5 bg-ink/95 backdrop-blur-xl">
            <nav class="px-6 py-4 flex flex-col gap-2 text-sm">
                <?php foreach ($adminNavLinks as $link) : ?>
                    <a class="<?= h($link['class']) ?> btn-md" href="<?= h($link['href']) ?>">
                        <?= h($link['label']) ?>
                    </a>
                <?php endforeach; ?>
                <div class="h-px bg-white/5 my-2"></div>
                <?php foreach ($adminNavActions as $link) : ?>
                    <a class="<?= h($link['class']) ?> btn-md" href="<?= h($link['href']) ?>">
                        <?= h($link['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    <?php endif; ?>
</header>

<?php if ($showAdminNav) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('adminMenuButton');
            const menu = document.getElementById('adminMobileMenu');

            if (!button || !menu) {
                return;
            }

            button.addEventListener('click', () => {
                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', String(!isExpanded));
                menu.classList.toggle('hidden');
            });
        });
    </script>
<?php endif; ?>

<main class="<?= h($mainClass) ?>">

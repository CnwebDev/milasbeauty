<?php
$navHomeHref = $navHomeHref ?? '/';
$navPricesHref = $navPricesHref ?? '/prijzen.php';
$navContactHref = $navContactHref ?? '#contact';
?>

<header class="sticky top-0 z-50 bg-headerPink">
    <div class="mx-auto max-w-container px-4">
        <div class="flex h-[55px] items-center justify-between">
            <a href="<?php echo htmlspecialchars($navHomeHref, ENT_QUOTES); ?>" class="flex items-center gap-3">
                <img
                    src="https://www.beautybymilasujeiry.nl/wp-content/uploads/2025/04/WhatsApp-Image-2025-04-16-at-21.27.36.jpeg"
                    alt="Mila Beauty"
                    class="h-10 w-10 rounded-full object-cover"
                />
                <span class="sr-only">Home</span>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-white">
                <a class="hover:opacity-90" href="<?php echo htmlspecialchars($navHomeHref, ENT_QUOTES); ?>">Home</a>
                <a class="hover:opacity-90" href="<?php echo htmlspecialchars($navPricesHref, ENT_QUOTES); ?>">Prijzen</a>
                <a class="hover:opacity-90" href="https://salonkee.nl/salon/milas-beauty">Afspraak maken</a>
                <a class="hover:opacity-90" href="<?php echo htmlspecialchars($navContactHref, ENT_QUOTES); ?>">Contact</a>

                <div class="relative group">
                    <a class="hover:opacity-90" href="/store">Store</a>
                    <div class="absolute left-0 mt-2 hidden min-w-[180px] rounded-md bg-headerPink/95 shadow-card group-hover:block">
                        <a class="block px-4 py-2 text-white hover:bg-white/10" href="/tassen">Tassen</a>
                        <a class="block px-4 py-2 text-white hover:bg-white/10" href="/kleding">Kleding</a>
                        <a class="block px-4 py-2 text-white hover:bg-white/10" href="/pakjes">Pakjes</a>
                    </div>
                </div>

                <a href="/cart" class="ml-2 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10" aria-label="Winkelwagen">
                    🛒
                </a>
            </nav>

            <button id="mobileBtn" class="md:hidden text-white text-2xl" aria-label="Menu">☰</button>
        </div>

        <div id="mobileMenu" class="md:hidden hidden pb-4 text-white">
            <div class="flex flex-col gap-3">
                <a href="<?php echo htmlspecialchars($navHomeHref, ENT_QUOTES); ?>">Home</a>
                <a href="<?php echo htmlspecialchars($navPricesHref, ENT_QUOTES); ?>">Prijzen</a>
                <a href="https://salonkee.nl/salon/milas-beauty">Afspraak maken</a>
                <a href="<?php echo htmlspecialchars($navContactHref, ENT_QUOTES); ?>">Contact</a>
                <a href="/store">Store</a>
            </div>
        </div>
    </div>
</header>

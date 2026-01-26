<?php
$navHomeHref = '/';
$navPricesHref = '/prijzen.php';
$navContactHref = '#contact';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Fonts (zelfde als WP: Montserrat + Taviraj) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Taviraj:wght@600;700&display=swap" rel="stylesheet">

    <!-- Tailwind (CDN voor snelle demo). In productie: build via Vite/CLI -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandPink: "#ff80d5",
                        brandPinkSoft: "#ffccef",
                        brandBg: "#f4f3ef",
                        brandAccent: "#CA9A8E",
                        brandText: "#404447",
                        headerPink: "rgba(255,128,213,0.61)",
                    },
                    fontFamily: {
                        sans: ["Montserrat", "ui-sans-serif", "system-ui"],
                        serif: ["Taviraj", "ui-serif", "Georgia"],
                    },
                    boxShadow: {
                        card: "4px 4px 8px 0px rgba(0,0,0,0.1)",
                        img: "4px 4px 20px 0px rgba(132,153,148,0.5)",
                        insetGlow: "0 0 10px 0 #CA9A8E inset, 0 0 20px 2px #CA9A8E",
                    },
                    borderRadius: {
                        fancy: "20px 0px 20px 0px",
                        fancyImg: "100px 0px 0px 0px",
                    },
                    maxWidth: { container: "1220px" },
                }
            }
        }
    </script>

    <link rel="stylesheet" href="/assets/css/theme.css" />

    <title>Mila Beauty</title>
</head>

<body class="text-brandText font-sans">
<!-- HEADER -->
<?php require __DIR__ . '/partials/navbar.php'; ?>

<!-- HERO -->
<section class="section-hero py-12">
    <div class="mx-auto max-w-container px-4">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 md:items-center">
            <div>
                <h1 class="font-serif text-4xl md:text-5xl leading-tight text-brandText">
                    Mila’s <span class="heading-highlight">Beauty</span> in Den-Haag
                </h1>
                <p class="mt-4 max-w-xl">
                    Jouw plek voor ontspanning en zelfverzorging. Of je nu behoefte hebt aan een verfrissende
                    gezichtsbehandeling, een strakke wenkbrauwstyling of een moment van pure ontspanning,
                    wij helpen je om je mooiste zelf naar voren te brengen.
                </p>

                <div class="mt-6">
                    <a class="btn-primary" href="https://salonkee.nl/salon/milas-beauty">Bekijk de prijzen</a>
                </div>
            </div>

            <div class="relative group">
                <div class="overflow-hidden rounded-2xl">
                    <img
                        class="w-full transition duration-300 group-hover:scale-110 group-hover:brightness-90"
                        src="assets/homepage/Naamloos.jpg"
                        alt=""
                    />
                </div>

                <!-- Video “icon” zoals Divi -->
                <button id="openVideo" class="absolute bottom-4 right-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-white/70 text-2xl shadow-card">
                    ▶
                </button>
            </div>
        </div>
    </div>
</section>

<!-- VIDEO MODAL (Divi et-lb-content) -->
<div id="videoModal" class="fixed inset-0 z-[999] hidden">
    <div class="absolute inset-0 bg-black/80"></div>
    <div class="relative mx-auto mt-10 max-w-4xl px-4">
        <button id="closeVideo" class="absolute -right-2 -top-10 text-brandAccent text-5xl font-bold">×</button>
        <div class="overflow-hidden rounded-xl bg-black shadow-card">
            <video controls class="w-full">
                <source type="video/mp4" src="https://www.beautybymilasujeiry.nl/wp-content/uploads/2025/03/WhatsApp-Video-2025-03-10-at-18.22.18.mp4">
            </video>
        </div>
    </div>
</div>

<!-- ABOUT -->
<section id="about" class="section-about py-16 relative">
    <div class="mx-auto max-w-container px-4">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 md:items-center">
            <div class="relative">
                <div class="overflow-hidden shadow-img about-image">
                    <img class="w-full object-cover" src="assets/homepage/beauty-about.jpg" alt="">
                </div>

                <!-- Contact card overlay -->
                <div class="absolute -bottom-10 -right-6 hidden md:block bg-white rounded-xl shadow-card p-6">
                    <h4 class="font-serif font-semibold text-[28px] leading-[30px] text-brandPink text-center">Contact informatie</h4>
                    <p class="mt-3 text-center font-sans font-semibold text-[20px] leading-[30px]">
                        <a class="text-brandText" href="mailto:info@beautybymilasujeiry.nl">info@beautybymilasujeiry.nl</a><br>
                        +31 6 111 222 333
                    </p>
                </div>
            </div>

            <div>
                <h2 class="font-serif text-[44px] leading-[50px] text-brandText">
                    Welkom bij<br>
                    <span class="heading-highlight">Beauty</span> by Mila
                </h2>

                <div class="h-3"></div>

                <p class="mt-2 text-[16px] leading-[26px]">
                    Bij ons draait alles om jouw schoonheid en welzijn. Stap binnen in een oase van rust en laat je
                    verwennen door onze ervaren specialisten. Jij verdient het om te stralen!
                </p>
            </div>
        </div>

        <!-- shape overlay links (absolute) -->
        <img
            class="pointer-events-none hidden md:block absolute left-0 top-0 w-[280px]"
            src="assets/homepage/beauty-shape-color.png"
            alt=""
        />
    </div>
</section>

<!-- SERVICES -->
<section id="services" class="section-services py-16 relative">
    <div class="mx-auto max-w-container px-4">
        <div class="text-center">
            <img class="mx-auto h-[75px] w-[76px]" src="assets/homepage/beauty-seprate-color.png" alt="">
            <h2 class="mt-3 font-serif text-[44px] leading-[50px]">
                Onze <span class="heading-highlight">Service</span>
            </h2>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Card 1 (altijd wit in Divi) -->
            <article class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="relative h-[260px] w-[260px] -mt-10 -ml-10 rounded-full overflow-hidden">
                    <img class="h-full w-full object-cover" src="assets/homepage/WhatsApp-Image-2025-03-23-at-11.39.23-1.jpeg" alt="">
                </div>
                <div class="px-5 pb-7">
                    <h4 class="font-serif font-bold text-[30px] text-brandPink text-center">Manicure</h4>
                    <p class="mt-2 text-center leading-[26px]">Geef je handen de verzorging die ze verdienen met een professionele manicure. Perfecte nagels, gezonde handen!</p>
                </div>
            </article>

            <!-- Card hover (zoals Divi columns hover) -->
            <article class="rounded-xl overflow-hidden transition hover:bg-white hover:shadow-card">
                <div class="relative h-[260px] w-[260px] -mt-10 -ml-10 rounded-full overflow-hidden">
                    <img class="h-full w-full object-cover" src="assets/homepage/beauty-body-treatment.jpg" alt="">
                </div>
                <div class="px-5 pb-7">
                    <h4 class="font-serif font-bold text-[30px] text-brandPink text-center">Afslank massage</h4>
                    <p class="mt-2 text-center leading-[26px]">Stimuleer je stofwisseling en verminder cellulitis met onze effectieve afslankmassages. Voel je lichter en energieker!</p>
                </div>
            </article>

            <article class="rounded-xl overflow-hidden transition hover:bg-white hover:shadow-card">
                <div class="relative h-[260px] w-[260px] -mt-10 -ml-10 rounded-full overflow-hidden">
                    <img class="h-full w-full object-cover" src="assets/homepage/WhatsApp-Image-2025-03-23-at-11.39.231.jpeg" alt="">
                </div>
                <div class="px-5 pb-7">
                    <h4 class="font-serif font-bold text-[30px] text-brandPink text-center">Ontharen</h4>
                    <p class="mt-2 text-center leading-[26px]">Zijdezachte huid zonder gedoe! Kies voor pijnloze en langdurige ontharing met onze professionele technieken.</p>
                </div>
            </article>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- herhaal voor 3 cards -->
            <article class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="relative h-[260px] w-[260px] -mt-10 -ml-10 rounded-full overflow-hidden">
                    <img class="h-full w-full object-cover" src="assets/homepage/WhatsApp-Image-2025-03-23-at-11.39.19.jpeg" alt="">
                </div>
                <div class="px-5 pb-7">
                    <h4 class="font-serif font-bold text-[30px] text-brandPink text-center">Haar behandeling</h4>
                    <p class="mt-2 text-center leading-[26px]">Van glanzend gezond haar tot een complete make-over – onze haarbehandelingen geven jouw look een boost!</p>
                </div>
            </article>

            <article class="rounded-xl overflow-hidden transition hover:bg-white hover:shadow-card">
                <div class="relative h-[260px] w-[260px] -mt-10 -ml-10 rounded-full overflow-hidden">
                    <img class="h-full w-full object-cover" src="assets/homepage/WhatsApp-Image-2025-03-23-at-11.39.24.jpeg" alt="">
                </div>
                <div class="px-5 pb-7">
                    <h4 class="font-serif font-bold text-[30px] text-brandPink text-center">Wimpers en wenkbrauwen</h4>
                    <p class="mt-2 text-center leading-[26px]">Geef je blik extra power met perfect gestylde wimpers en wenkbrauwen. Laat je ogen stralen!</p>
                </div>
            </article>

            <article class="rounded-xl overflow-hidden transition hover:bg-white hover:shadow-card">
                <div class="relative h-[260px] w-[260px] -mt-10 -ml-10 rounded-full overflow-hidden">
                    <img class="h-full w-full object-cover" src="assets/homepage/WhatsApp-Image-2025-03-23-at-11.39.221.jpeg" alt="">
                </div>
                <div class="px-5 pb-7">
                    <h4 class="font-serif font-bold text-[30px] text-brandPink text-center">Nog veel meer</h4>
                    <p class="mt-2 text-center leading-[26px]">Met ons team van specialisten staan we klaar om jouw beauty uitdaging aan te pakken en je weer laten stralen.</p>
                </div>
            </article>
        </div>

        <div class="mt-10 text-center">
            <a class="btn-primary" href="https://salonkee.nl/salon/milas-beauty">Maak een afspraak</a>
        </div>

        <!-- right shape overlay -->
        <img
            class="pointer-events-none hidden md:block absolute right-0 top-0 w-[280px]"
            src="assets/homepage/beauty-shape-color.png"
            alt=""
        />
    </div>
</section>

<!-- FACTS -->
<section class="section-facts py-16">
    <div class="mx-auto max-w-container px-4">
        <div class="rounded-xl md:rounded-none">
            <div class="flex flex-col-reverse items-center gap-8 md:flex-row">
                <div class="w-full md:w-[60%]">
                    <div class="text-right">
                        <h2 class="font-serif text-[44px] leading-[50px] ml-auto max-w-[400px]">
                            De belangrijkste <span class="heading-highlight">cijfers</span>
                        </h2>
                        <p class="mt-3 max-w-xl ml-auto">
                            Cijfers waar wij trots op zijn en jou de ervaring geven die je verwacht van een beauty salon!
                        </p>
                    </div>

                    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-3 text-center">
                        <div>
                            <div class="text-5xl font-serif font-bold" data-count="25">0</div>
                            <div class="mt-2">Beauty behandelingen</div>
                        </div>
                        <div>
                            <div class="text-5xl font-serif font-bold" data-count="6">0</div>
                            <div class="mt-2">Specialisten</div>
                        </div>
                        <div>
                            <div class="text-5xl font-serif font-bold" data-count="19">0</div>
                            <div class="mt-2">Jaar ervaring</div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-[calc(40%+50px)] md:-ml-[50px]">
                    <img class="w-full" src="assets/homepage/beauty-facts1.png" alt="">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- REVIEWS -->
<section class="section-reviews py-16">
    <div class="mx-auto max-w-container px-4">
        <div class="text-center">
            <img class="mx-auto h-[75px] w-[76px]" src="assets/homepage/beauty-seprate-color.png" alt="">
            <h2 class="mt-3 font-serif text-[44px] leading-[50px]">Reviews van</h2>
            <h2 class="font-serif text-[44px] leading-[50px]"><span class="heading-highlight">klanten</span></h2>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
            <article class="relative rounded-xl bg-white/10 p-6 shadow-card overflow-hidden">
                <div class="h-20 w-20 rounded-full bg-cover bg-center" style="background-image:url('assets/homepage/WhatsApp-Image-2025-03-23-at-11.39.24.jpeg')"></div>
                <div class="relative mt-4 border-l border-white/50 pl-5 quote-watermark">
                    <p>“Wat een geweldige ervaring! ... Echt een aanrader!”</p>
                </div>
                <div class="mt-4 font-bold">SOPHIE</div>
            </article>

            <article class="relative rounded-xl bg-white/10 p-6 shadow-card overflow-hidden">
                <div class="relative mt-4 border-r border-white/50 pr-5 quote-watermark">
                    <p>“Ik heb een afslankmassage geboekt ... Ik kom hier zeker terug!”</p>
                </div>
                <div class="mt-4 font-bold text-right">EMMA</div>
            </article>

            <article class="relative rounded-xl bg-white/10 p-6 shadow-card overflow-hidden">
                <div class="h-20 w-20 rounded-full bg-cover bg-center" style="background-image:url('assets/homepage/WhatsApp-Image-2025-03-23-at-11.43.15.jpeg')"></div>
                <div class="relative mt-4 border-l border-white/50 pl-5 quote-watermark">
                    <p>“Mijn manicure was tot in de puntjes verzorgd! ...”</p>
                </div>
                <div class="mt-4 font-bold">FATIMA</div>
            </article>
        </div>
    </div>
</section>

<!-- FAQ + CTA -->
<section class="section-faq py-16">
    <div class="mx-auto max-w-container px-4">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-5 md:items-start">
            <div class="md:col-span-2">
                <h4 class="font-serif text-2xl">Maak een afspraak</h4>
                <p class="mt-2">Plan eenvoudig een afspraak in wanneer jou het best uitkomt</p>
                <div class="mt-6">
                    <a class="btn-primary" href="https://salonkee.nl/salon/milas-beauty">Bekijk de prijzen</a>
                </div>
            </div>

            <div class="md:col-span-3">
                <h2 class="font-serif text-4xl">Veel gestelde vragen</h2>

                <div class="mt-6 space-y-3">
                    <details class="rounded-xl bg-white p-4 shadow-card">
                        <summary class="cursor-pointer font-semibold">Hoelang duurt een afspraak</summary>
                        <p class="mt-2">Aenean mattis dapibus aliquam...</p>
                    </details>

                    <details class="rounded-xl bg-white p-4 shadow-card">
                        <summary class="cursor-pointer font-semibold">Wat als ik niet kan komen</summary>
                        <p class="mt-2">Aenean mattis dapibus aliquam...</p>
                    </details>

                    <details class="rounded-xl bg-white p-4 shadow-card">
                        <summary class="cursor-pointer font-semibold">Wat als ik niet tevreden ben</summary>
                        <p class="mt-2">Aenean mattis dapibus aliquam...</p>
                    </details>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer id="contact" class="section-footer py-10">
    <div class="mx-auto max-w-container px-4 text-center">
        <div>
            <h6 class="font-semibold">Email</h6>
            <p>info@beautybymilasujeiry.com</p>
        </div>

        <div class="mt-8 text-sm">
            Mede mogelijk gemaakt door 2026 <a class="underline" href="https://racdigital.nl">RAC DIGITAL</a>
        </div>
    </div>
</footer>

<script>
    // Mobile menu
    const mobileBtn = document.getElementById('mobileBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    mobileBtn?.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));

    // Video modal
    const openVideo = document.getElementById('openVideo');
    const closeVideo = document.getElementById('closeVideo');
    const videoModal = document.getElementById('videoModal');
    openVideo?.addEventListener('click', () => videoModal.classList.remove('hidden'));
    closeVideo?.addEventListener('click', () => videoModal.classList.add('hidden'));
    videoModal?.addEventListener('click', (e) => {
        if (e.target === videoModal) videoModal.classList.add('hidden');
    });

    // Simple counters (Facts)
    const counters = [...document.querySelectorAll('[data-count]')];
    const animateCounters = () => {
        counters.forEach(el => {
            const target = Number(el.getAttribute('data-count'));
            let cur = 0;
            const step = Math.max(1, Math.round(target / 40));
            const t = setInterval(() => {
                cur += step;
                if (cur >= target) { cur = target; clearInterval(t); }
                el.textContent = String(cur);
            }, 25);
        });
    };
    // Trigger once
    let done = false;
    const onScroll = () => {
        if (done) return;
        const section = counters[0]?.closest('section');
        if (!section) return;
        const r = section.getBoundingClientRect();
        if (r.top < window.innerHeight * 0.8) { done = true; animateCounters(); }
    };
    window.addEventListener('scroll', onScroll);
    onScroll();
</script>
</body>
</html>

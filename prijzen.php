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
    <link rel="stylesheet" href="/assets/css/prijzen.css" />

    <title>Prijzen | Mila Beauty</title>
</head>

<body class="text-brandText font-sans">

<!-- HEADER (zelfde structuur als je home) -->
<?php require __DIR__ . '/partials/navbar.php'; ?>

<!-- HERO -->
<section class="section-pricing py-12">
    <div class="mx-auto max-w-container px-4">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 md:items-center">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="pill">Alle prijzen in €</span>
                    <span class="pill">v.a. = vanaf</span>
                    <span class="pill">Zetfouten voorbehouden</span>
                </div>

                <h1 class="mt-4 font-serif text-4xl md:text-5xl leading-tight text-brandText">
                    Onze <span class="heading-highlight">Prijzen</span>
                </h1>

                <p class="mt-4 max-w-xl">
                    Hieronder vind je de volledige prijslijst. Gebruik de zoekbalk om snel een behandeling te vinden,
                    of klik op een categorie.
                </p>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a class="btn-primary" href="https://salonkee.nl/salon/milas-beauty">Afspraak maken</a>
                    <a class="btn-primary" href="#openingstijden">Openingstijden</a>
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-card p-5">
                <label class="text-sm font-semibold" for="priceSearch">Zoeken in behandelingen</label>
                <div class="mt-2 flex gap-2">
                    <input
                        id="priceSearch"
                        type="text"
                        placeholder="Bijv. gellak, wax, knippen, microblading..."
                        class="w-full rounded-xl border border-black/10 bg-brandBg px-4 py-3 outline-none focus:ring-2 focus:ring-brandPink/40"
                    />
                    <button id="clearSearch" class="rounded-xl border border-black/10 bg-white px-4 font-semibold hover:bg-black/5" aria-label="Wis zoeken">
                        ✕
                    </button>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a class="pill hover:opacity-80" href="#nagels">Nagels</a>
                    <a class="pill hover:opacity-80" href="#kapper">Kapper</a>
                    <a class="pill hover:opacity-80" href="#gezicht">Gezicht</a>
                    <a class="pill hover:opacity-80" href="#wimpers">Wimpers</a>
                    <a class="pill hover:opacity-80" href="#wenkbrauwen">Wenkbrauwen</a>
                    <a class="pill hover:opacity-80" href="#pmu">PMU</a>
                    <a class="pill hover:opacity-80" href="#waxing">Waxing</a>
                    <a class="pill hover:opacity-80" href="#laser-ontharen">Laser</a>
                </div>

                <p id="noResults" class="mt-4 hidden rounded-xl bg-brandBg p-4 text-sm">
                    Geen resultaten gevonden. Probeer een ander zoekwoord.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- PRICES GRID -->
<section class="section-pricing-search pb-16">
    <div class="mx-auto max-w-container px-4">

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <!-- NAGELS -->
            <article id="nagels" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Nagels &amp; <span class="heading-highlight">Manicure</span></h2>
                    <p class="mt-2 opacity-90">Manicure, gellak, BIAB, sets en extra’s.</p>
                </div>

                <div class="p-6 space-y-6">

                    <div>
                        <h3 class="font-serif text-xl text-brandPink">Nagels Manicure</h3>

                        <div class="mt-3 price-row price-item" data-name="manicure polish">
                            <div><div class="price-name">Manicure polish</div></div>
                            <div class="price-val">€ 30</div>
                        </div>

                        <div class="price-row price-item" data-name="manicure gellak">
                            <div><div class="price-name">Manicure gellak</div></div>
                            <div class="price-val">€ 35</div>
                        </div>

                        <div class="price-row price-item" data-name="manicure biab">
                            <div><div class="price-name">Manicure BIAB</div></div>
                            <div class="price-val">€ 40</div>
                        </div>

                        <div class="price-row price-item" data-name="alleen gellak">
                            <div><div class="price-name">Alleen gellak</div></div>
                            <div class="price-val">€ 25</div>
                        </div>

                        <div class="price-row price-item" data-name="alleen nagellak">
                            <div><div class="price-name">Alleen nagellak</div></div>
                            <div class="price-val">€ 20</div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-serif text-xl text-brandPink">Nagelsets</h3>

                        <div class="mt-3 price-row price-item" data-name="nieuwe set acryl kort french">
                            <div>
                                <div class="price-name">Nieuwe set “acryl kort”</div>
                                <div class="price-desc">Kleur naar keuze (French)</div>
                            </div>
                            <div class="price-val">€ 65</div>
                        </div>

                        <div class="price-row price-item" data-name="nieuwe set acryl lang french">
                            <div>
                                <div class="price-name">Nieuwe set “acryl lang”</div>
                                <div class="price-desc">Kleur naar keuze (French)</div>
                            </div>
                            <div class="price-val">€ 75</div>
                        </div>

                        <div class="price-row price-item" data-name="nieuwe set gel nails">
                            <div>
                                <div class="price-name">Nieuwe set “gel nails”</div>
                                <div class="price-desc">Kleur naar keuze</div>
                            </div>
                            <div class="price-val">€ 65</div>
                        </div>

                        <div class="price-row price-item" data-name="nieuwe set gel french">
                            <div><div class="price-name">Nieuwe set “gel French”</div></div>
                            <div class="price-val">€ 75</div>
                        </div>

                        <div class="price-row price-item" data-name="opvullen acryl">
                            <div><div class="price-name">Opvullen acryl</div></div>
                            <div class="price-val">€ 45</div>
                        </div>

                        <div class="price-row price-item" data-name="opvullen gellak">
                            <div><div class="price-name">Opvullen gellak</div></div>
                            <div class="price-val">€ 50</div>
                        </div>

                        <div class="price-row price-item" data-name="verwijderen acryl">
                            <div><div class="price-name">Verwijderen acryl</div></div>
                            <div class="price-val">€ 15</div>
                        </div>

                        <div class="price-row price-item" data-name="verwijderen gellak">
                            <div><div class="price-name">Verwijderen gellak</div></div>
                            <div class="price-val">€ 10</div>
                        </div>

                        <div class="price-row price-item" data-name="verwijderen nagellak">
                            <div><div class="price-name">Verwijderen nagellak</div></div>
                            <div class="price-val">€ 5</div>
                        </div>

                        <div class="price-row price-item" data-name="reconstructie per nagel">
                            <div><div class="price-name">Reconstructie</div><div class="price-desc">Per nagel</div></div>
                            <div class="price-val">€ 5</div>
                        </div>

                        <div class="price-row price-item" data-name="press on deal natural">
                            <div><div class="price-name">Press on deal (natural)</div></div>
                            <div class="price-val">€ 45</div>
                        </div>

                        <div class="price-row price-item" data-name="press on deal lang deco">
                            <div><div class="price-name">Press on deal (lang + deco)</div></div>
                            <div class="price-val">€ 55</div>
                        </div>

                        <div class="price-row price-item" data-name="steentjes per nagel">
                            <div><div class="price-name">Steentjes</div><div class="price-desc">Per nagel</div></div>
                            <div class="price-val">€ 3</div>
                        </div>

                        <div class="price-row price-item" data-name="press on deal vanaf">
                            <div><div class="price-name">Press on deal</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 45</div>
                        </div>

                        <div class="price-row price-item" data-name="saturday summer deal vanaf">
                            <div><div class="price-name">Saturday summer deal</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 50</div>
                        </div>

                        <div class="price-row price-item" data-name="french deco per nagel">
                            <div><div class="price-name">French Deco</div><div class="price-desc">Per nagel</div></div>
                            <div class="price-val">€ 5</div>
                        </div>
                    </div>

                </div>
            </article>

            <!-- PEDICURE + LICHAAM -->
            <article class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Pedicure &amp; <span class="heading-highlight">Lichaam</span></h2>
                    <p class="mt-2 opacity-90">Voetenverzorging en massages/behandelingen.</p>
                </div>

                <div class="p-6 space-y-6">

                    <div>
                        <h3 class="font-serif text-xl text-brandPink">Pedicure</h3>

                        <div class="mt-3 price-row price-item" data-name="pedicure basic kleur of french">
                            <div><div class="price-name">Pedicure Basic</div><div class="price-desc">Kleur of French</div></div>
                            <div class="price-val">€ 45</div>
                        </div>

                        <div class="price-row price-item" data-name="pedicure spa scrub massage verwijderen dode cellen">
                            <div>
                                <div class="price-name">Pedicure Spa</div>
                                <div class="price-desc">Scrub + massage, verwijderen dode cellen, etc.</div>
                            </div>
                            <div class="price-val">€ 55</div>
                        </div>

                        <div class="price-row price-item" data-name="pedicure acryl per nagel">
                            <div><div class="price-name">Pedicure acryl</div><div class="price-desc">Per nagel</div></div>
                            <div class="price-val">€ 5</div>
                        </div>

                        <div class="price-row price-item" data-name="parafine behandeling">
                            <div><div class="price-name">Paraffine behandeling</div></div>
                            <div class="price-val">€ 30</div>
                        </div>

                        <div class="price-row price-item" data-name="medische pedicure">
                            <div><div class="price-name">Medische pedicure</div></div>
                            <div class="price-val">€ 75</div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-serif text-xl text-brandPink">Lichaams behandelingen</h3>

                        <div class="mt-3 price-row price-item" data-name="body scrub deep hydration">
                            <div><div class="price-name">Body scrub + deep hydration</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 100</div>
                        </div>

                        <div class="price-row price-item" data-name="afslankmassages per behandeling">
                            <div><div class="price-name">Afslankmassages</div><div class="price-desc">Per behandeling (v.a.)</div></div>
                            <div class="price-val">v.a. € 60</div>
                        </div>

                        <div class="price-row price-item" data-name="10 behandelingen voor 500 afslank">
                            <div><div class="price-name">10 behandelingen</div><div class="price-desc">Pakketprijs</div></div>
                            <div class="price-val">€ 500</div>
                        </div>

                        <div class="price-row price-item" data-name="maderotherapy">
                            <div><div class="price-name">Maderotherapy</div></div>
                            <div class="price-val">€ 80</div>
                        </div>

                        <div class="price-row price-item" data-name="aromatherapie">
                            <div><div class="price-name">Aromatherapie</div></div>
                            <div class="price-val">€ 10</div>
                        </div>

                        <div class="price-row price-item" data-name="sport massage">
                            <div><div class="price-name">Sport massage</div></div>
                            <div class="price-val">€ 90</div>
                        </div>

                        <div class="price-row price-item" data-name="relaxing reducing massage">
                            <div><div class="price-name">Relaxing / Reducing massage</div></div>
                            <div class="price-val">€ 80</div>
                        </div>

                        <div class="price-row price-item" data-name="full body massage">
                            <div><div class="price-name">Full body massage</div></div>
                            <div class="price-val">€ 150</div>
                        </div>
                    </div>

                </div>
            </article>

            <!-- KAPPER -->
            <article id="kapper" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Kapper &amp; <span class="heading-highlight">Haar</span></h2>
                    <p class="mt-2 opacity-90">Knippen, wassen, föhnen, styling en behandelingen.</p>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="font-serif text-xl text-brandPink">Kapper</h3>

                        <div class="mt-3 price-row price-item" data-name="knippen dames">
                            <div><div class="price-name">Knippen dames</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 30</div>
                        </div>

                        <div class="price-row price-item" data-name="knippen halflang tot lang haar">
                            <div><div class="price-name">Knippen halflang tot lang haar</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 35</div>
                        </div>

                        <div class="price-row price-item" data-name="wassen knippen drogen">
                            <div><div class="price-name">Wassen, knippen &amp; drogen</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 35</div>
                        </div>

                        <div class="price-row price-item" data-name="wassen knippen drogen halflang tot lang haar">
                            <div><div class="price-name">Wassen, knippen &amp; drogen</div><div class="price-desc">Halflang tot lang haar (v.a.)</div></div>
                            <div class="price-val">v.a. € 40</div>
                        </div>

                        <div class="price-row price-item" data-name="moeilijk haar knippen toeslag">
                            <div><div class="price-name">Moeilijk haar knippen</div><div class="price-desc">Toeslag</div></div>
                            <div class="price-val">+ € 5</div>
                        </div>

                        <div class="price-row price-item" data-name="lang dik haar knippen toeslag">
                            <div><div class="price-name">Lang / dik haar knippen</div><div class="price-desc">Toeslag</div></div>
                            <div class="price-val">+ € 5</div>
                        </div>

                        <div class="price-row price-item" data-name="wassen knippen fohnen">
                            <div><div class="price-name">Wassen, knippen &amp; föhnen</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 60</div>
                        </div>

                        <div class="price-row price-item" data-name="wassen fohnen">
                            <div><div class="price-name">Wassen &amp; föhnen</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 35</div>
                        </div>

                        <div class="price-row price-item" data-name="wassen watergolven">
                            <div><div class="price-name">Wassen watergolven</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 45</div>
                        </div>

                        <div class="price-row price-item" data-name="wassen tangen stomen">
                            <div><div class="price-name">Wassen tangen (stomen)</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 35</div>
                        </div>

                        <div class="price-row price-item" data-name="wassen krullen">
                            <div><div class="price-name">Wassen krullen</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 35</div>
                        </div>

                        <div class="price-row price-item" data-name="moeilijk haar krullen toeslag">
                            <div><div class="price-name">Moeilijk haar krullen</div><div class="price-desc">Toeslag</div></div>
                            <div class="price-val">+ € 15</div>
                        </div>

                        <div class="price-row price-item" data-name="lang dik haar krullen toeslag">
                            <div><div class="price-name">Lang / dik haar krullen</div><div class="price-desc">Toeslag</div></div>
                            <div class="price-val">+ € 15</div>
                        </div>

                        <div class="price-row price-item" data-name="hoofdhuid massage">
                            <div><div class="price-name">Hoofdhuid massage</div></div>
                            <div class="price-val">€ 20</div>
                        </div>

                        <div class="price-row price-item" data-name="kroes haar tangen">
                            <div><div class="price-name">Kroes haar tangen</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 45</div>
                        </div>

                        <div class="price-row price-item" data-name="extra werk kroes haar toeslag">
                            <div><div class="price-name">Extra werk kroes haar</div><div class="price-desc">Toeslag</div></div>
                            <div class="price-val">+ € 20</div>
                        </div>

                        <div class="price-row price-item" data-name="wassen drogen">
                            <div><div class="price-name">Wassen &amp; drogen</div></div>
                            <div class="price-val">€ 20</div>
                        </div>

                        <div class="price-row price-item" data-name="masocapilotera">
                            <div><div class="price-name">Masocapilotera</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 80</div>
                        </div>

                        <div class="price-row price-item" data-name="herenkapsels">
                            <div><div class="price-name">Herenkapsels</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 25</div>
                        </div>

                        <div class="price-row price-item" data-name="kapsel jongens">
                            <div><div class="price-name">Kapsel jongens</div></div>
                            <div class="price-val">€ 20</div>
                        </div>

                        <div class="price-row price-item" data-name="kapsels meisjes">
                            <div><div class="price-name">Kapsels meisjes</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 25</div>
                        </div>

                        <div class="price-row price-item" data-name="kapsels met vlechten">
                            <div><div class="price-name">Kapsels met vlechten</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 35</div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-serif text-xl text-brandPink">Behandelingen</h3>

                        <div class="mt-3 price-row price-item" data-name="kreatine behandeling">
                            <div><div class="price-name">Kreatine behandeling</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 150</div>
                        </div>

                        <div class="price-row price-item" data-name="haarbotox">
                            <div><div class="price-name">Haarbotox</div><div class="price-desc">v.a.</div></div>
                            <div class="price-val">v.a. € 150</div>
                        </div>

                        <div class="mt-3 rounded-xl bg-brandBg p-4 text-sm leading-relaxed">
                            <span class="font-semibold">Info:</span>
                            Een behandelingsoplossing waarbij vitamines, eiwitten en collageen worden gebruikt om de vitaliteit
                            en het zelfherstellend vermogen van uw haar te activeren.
                        </div>
                    </div>
                </div>
            </article>

            <!-- HAAR EXTENSIONS -->
            <article class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Haar <span class="heading-highlight">Extensions</span></h2>
                    <p class="mt-2 opacity-90">Microring, braids en combinaties met styling.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="extensions microring braids">
                        <div><div class="price-name">Extensions microring &amp; braids</div></div>
                        <div class="price-val">€ 80</div>
                    </div>

                    <div class="price-row price-item" data-name="extensions brown hair">
                        <div><div class="price-name">Extensions brown hair</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 145</div>
                    </div>

                    <div class="price-row price-item" data-name="microring hairstyle">
                        <div><div class="price-name">Microring &amp; hairstyle</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 165</div>
                    </div>

                    <div class="price-row price-item" data-name="braids hairstyle">
                        <div><div class="price-name">Braids &amp; hairstyle</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 85</div>
                    </div>

                    <div class="price-row price-item" data-name="hair washing">
                        <div><div class="price-name">Hair washing</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 30</div>
                    </div>

                    <div class="price-row price-item" data-name="extensions blonde hair">
                        <div><div class="price-name">Extensions blonde hair</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 215</div>
                    </div>
                </div>
            </article>

            <!-- GEZICHT -->
            <article id="gezicht" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Gezichts<span class="heading-highlight">behandelingen</span></h2>
                    <p class="mt-2 opacity-90">Reiniging, depigmentatie en huidverbetering.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="diagnostic intake">
                        <div><div class="price-name">Diagnostic / intake</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 30</div>
                    </div>

                    <div class="price-row price-item" data-name="gezicht diep reiniging">
                        <div><div class="price-name">Gezicht diep reiniging</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 80</div>
                    </div>

                    <div class="price-row price-item" data-name="facial depigmentation">
                        <div><div class="price-name">Facial depigmentation</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 100</div>
                    </div>

                    <div class="price-row price-item" data-name="dermaplanning dermaplaning">
                        <div><div class="price-name">Dermaplanning</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 70</div>
                    </div>

                    <div class="price-row price-item" data-name="gezichtsmassage">
                        <div><div class="price-name">Gezichtsmassage</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 30</div>
                    </div>

                    <div class="price-row price-item" data-name="anti verouderings behandeling">
                        <div><div class="price-name">Anti verouderings behandeling</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 90</div>
                    </div>

                    <div class="price-row price-item" data-name="cauterization">
                        <div><div class="price-name">Cauterization</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 100</div>
                    </div>

                    <div class="price-row price-item" data-name="laser skin depigmentation">
                        <div><div class="price-name">Laser skin depigmentation</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 120</div>
                    </div>

                    <div class="price-row price-item" data-name="high frequency">
                        <div><div class="price-name">High Frequency</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 100</div>
                    </div>

                    <div class="price-row price-item" data-name="microdermabrasion">
                        <div><div class="price-name">Microdermabrasion</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 50</div>
                    </div>

                    <div class="price-row price-item" data-name="paleta ultrasonica">
                        <div><div class="price-name">Paleta ultrasonica</div></div>
                        <div class="price-val">€ 60</div>
                    </div>
                </div>
            </article>
            <!-- WIMPERS -->
            <article id="wimpers" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Wimper <span class="heading-highlight">Extensions</span></h2>
                    <p class="mt-2 opacity-90">Nieuwe sets, opvullen en verzorging.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="wimper extensions classic">
                        <div>
                            <div class="price-name">Nieuwe set ‘classic’</div>
                            <div class="price-desc">3 à 4 weken garantie</div>
                        </div>
                        <div class="price-val">€ 65</div>
                    </div>

                    <div class="price-row price-item" data-name="wimper extensions volume">
                        <div><div class="price-name">Nieuwe set ‘volume’</div></div>
                        <div class="price-val">€ 75</div>
                    </div>

                    <div class="price-row price-item" data-name="wimper extensions mega volume">
                        <div><div class="price-name">Nieuwe set ‘mega volume’</div></div>
                        <div class="price-val">€ 95</div>
                    </div>

                    <div class="price-row price-item" data-name="wimper extensions opvulling">
                        <div><div class="price-name">Opvulling</div></div>
                        <div class="price-val">€ 35</div>
                    </div>

                    <div class="price-row price-item" data-name="wimper extensions touch ups">
                        <div><div class="price-name">Touch ups</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 30</div>
                    </div>

                    <div class="price-row price-item" data-name="wimper extensions verwijderen lash bath">
                        <div><div class="price-name">Verwijderen &amp; lash bath</div></div>
                        <div class="price-val">€ 20</div>
                    </div>

                    <div class="price-row price-item" data-name="wimper lifting">
                        <div><div class="price-name">Lifting</div></div>
                        <div class="price-val">€ 35</div>
                    </div>

                    <div class="price-row price-item" data-name="wimpers verven">
                        <div><div class="price-name">Colouring lashes</div></div>
                        <div class="price-val">€ 10</div>
                    </div>
                </div>
            </article>

            <!-- WENKBRAUWEN -->
            <article id="wenkbrauwen" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Wenk<span class="heading-highlight">brauwen</span></h2>
                    <p class="mt-2 opacity-90">Vormen, kleuren en lamineren.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="wenkbrauwen laminatie">
                        <div><div class="price-name">Lamination</div></div>
                        <div class="price-val">€ 40</div>
                    </div>

                    <div class="price-row price-item" data-name="wenkbrauwen waxen epileren">
                        <div><div class="price-name">Waxen / epileren</div></div>
                        <div class="price-val">€ 12</div>
                    </div>

                    <div class="price-row price-item" data-name="wenkbrauwen verven">
                        <div><div class="price-name">Verven</div></div>
                        <div class="price-val">€ 15</div>
                    </div>

                    <div class="price-row price-item" data-name="wenkbrauwen verven waxen">
                        <div><div class="price-name">Verven &amp; waxen</div></div>
                        <div class="price-val">€ 30</div>
                    </div>

                    <div class="price-row price-item" data-name="wimpers verven wenkbrauwen">
                        <div><div class="price-name">Wimpers verven</div></div>
                        <div class="price-val">€ 15</div>
                    </div>
                </div>
            </article>

            <!-- PERMANENTE MAKE-UP -->
            <article id="pmu" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Permanente <span class="heading-highlight">Make-up</span></h2>
                    <p class="mt-2 opacity-90">PMU behandelingen en huidverbetering.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="pmu brilliant lips">
                        <div><div class="price-name">Brilliant lips</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 50</div>
                    </div>

                    <div class="price-row price-item" data-name="pmu moedervlek verwijderen">
                        <div><div class="price-name">Moedervlek verwijderen</div></div>
                        <div class="price-val">€ 40</div>
                    </div>

                    <div class="price-row price-item" data-name="microneedling">
                        <div><div class="price-name">Microneedling</div></div>
                        <div class="price-val">€ 90</div>
                    </div>

                    <div class="price-row price-item" data-name="microblading">
                        <div><div class="price-name">Microblading</div></div>
                        <div class="price-val">€ 250</div>
                    </div>

                    <div class="price-row price-item" data-name="micropigmentation">
                        <div><div class="price-name">Micropigmentation</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 180</div>
                    </div>
                </div>
            </article>
            <!-- WAXING -->
            <article id="waxing" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Wax<span class="heading-highlight">ing</span></h2>
                    <p class="mt-2 opacity-90">Snelle en strakke wax behandelingen.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="wax brazilian wax">
                        <div><div class="price-name">Brazilian wax</div></div>
                        <div class="price-val">€ 48</div>
                    </div>

                    <div class="price-row price-item" data-name="wax brazilian string">
                        <div><div class="price-name">Brazilian string</div></div>
                        <div class="price-val">€ 25</div>
                    </div>

                    <div class="price-row price-item" data-name="wax bikini">
                        <div><div class="price-name">Bikini</div></div>
                        <div class="price-val">€ 20</div>
                    </div>

                    <div class="price-row price-item" data-name="wax oksels">
                        <div><div class="price-name">Oksels</div></div>
                        <div class="price-val">€ 20</div>
                    </div>

                    <div class="price-row price-item" data-name="wax benen">
                        <div>
                            <div class="price-name">Benen</div>
                            <div class="price-desc">1 been € 28 • 2 benen € 40</div>
                        </div>
                        <div class="price-val">€ 28 / € 40</div>
                    </div>

                    <div class="price-row price-item" data-name="wax armen">
                        <div>
                            <div class="price-name">Armen</div>
                            <div class="price-desc">1 arm € 20 • 2 armen € 30</div>
                        </div>
                        <div class="price-val">€ 20 / € 30</div>
                    </div>

                    <div class="price-row price-item" data-name="wax billen">
                        <div><div class="price-name">Billen</div></div>
                        <div class="price-val">€ 15</div>
                    </div>

                    <div class="price-row price-item" data-name="wax rug">
                        <div><div class="price-name">Rug</div></div>
                        <div class="price-val">€ 25</div>
                    </div>

                    <div class="price-row price-item" data-name="wax borst">
                        <div><div class="price-name">Borst</div></div>
                        <div class="price-val">€ 40</div>
                    </div>

                    <div class="price-row price-item" data-name="wax navel lijn">
                        <div><div class="price-name">Navellijn</div></div>
                        <div class="price-val">€ 5</div>
                    </div>

                    <div class="price-row price-item" data-name="wax bovenlip">
                        <div><div class="price-name">Bovenlip</div></div>
                        <div class="price-val">€ 12</div>
                    </div>

                    <div class="price-row price-item" data-name="wax kin kaaklijn">
                        <div><div class="price-name">Kin / kaaklijn</div></div>
                        <div class="price-val">€ 8</div>
                    </div>

                    <div class="price-row price-item" data-name="wax bakkerbaard">
                        <div><div class="price-name">Bakkerbaard</div></div>
                        <div class="price-val">€ 5</div>
                    </div>

                    <div class="price-row price-item" data-name="wax neus">
                        <div><div class="price-name">Neus</div></div>
                        <div class="price-val">€ 8</div>
                    </div>

                    <div class="price-row price-item" data-name="wax hele gezicht">
                        <div><div class="price-name">Hele gezicht</div></div>
                        <div class="price-val">€ 25</div>
                    </div>
                </div>
            </article>

            <!-- LASER ONTHAREN -->
            <article id="laser-ontharen" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Laser <span class="heading-highlight">Ontharen</span></h2>
                    <p class="mt-2 opacity-90">Voor langdurig glad resultaat.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="laser oksels">
                        <div><div class="price-name">Oksels</div></div>
                        <div class="price-val">€ 35</div>
                    </div>

                    <div class="price-row price-item" data-name="laser bikini">
                        <div><div class="price-name">Bikini</div></div>
                        <div class="price-val">€ 40</div>
                    </div>

                    <div class="price-row price-item" data-name="laser benen armen oksels bikini">
                        <div><div class="price-name">Benen, armen, oksels &amp; bikini</div></div>
                        <div class="price-val">€ 150</div>
                    </div>

                    <div class="price-row price-item" data-name="laser bovenlip onderlip wangen">
                        <div><div class="price-name">Bovenlip, onderlip, wangen</div></div>
                        <div class="price-val">€ 70</div>
                    </div>

                    <div class="price-row price-item" data-name="laser voorhoofd wenkbrauwen">
                        <div><div class="price-name">Voorhoofd wenkbrauwen</div></div>
                        <div class="price-val">€ 70</div>
                    </div>
                </div>
            </article>

            <!-- TANDEN BLEKEN -->
            <article id="tanden-bleken" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Tanden <span class="heading-highlight">Bleken</span></h2>
                    <p class="mt-2 opacity-90">Stralende smile in één behandeling.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="tanden bleken bleekbehandeling">
                        <div><div class="price-name">Bleekbehandeling</div></div>
                        <div class="price-val">€ 80</div>
                    </div>
                </div>
            </article>

            <!-- VISAGIE -->
            <article id="visagie" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Visa<span class="heading-highlight">gie</span></h2>
                    <p class="mt-2 opacity-90">Make-up voor speciale momenten.</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="visagie bruids make-up">
                        <div><div class="price-name">Bruids make-up</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 60</div>
                    </div>

                    <div class="price-row price-item" data-name="visagie glamour make-up">
                        <div><div class="price-name">Glamour make-up</div><div class="price-desc">v.a.</div></div>
                        <div class="price-val">v.a. € 60</div>
                    </div>
                </div>
            </article>

            <!-- SMOOTHIES -->
            <article id="smoothies" class="rounded-2xl bg-white shadow-card overflow-hidden price-section">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Smoo<span class="heading-highlight">thies</span></h2>
                    <p class="mt-2 opacity-90">Gezonde smoothies van vers fruit (ook to go).</p>
                </div>

                <div class="p-6">
                    <div class="price-row price-item" data-name="smoothies gezond vers fruit">
                        <div><div class="price-name">Smoothie (to go)</div></div>
                        <div class="price-val">€ 6</div>
                    </div>
                </div>
            </article>

        </div><!-- /grid -->
    </div>
</section>

<!-- WEET U DAT + OPENINGSTIJDEN -->
<section id="openingstijden" class="section-pricing pb-16">
    <div class="mx-auto max-w-container px-4">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <article class="rounded-2xl bg-white shadow-card overflow-hidden">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Weet u <span class="heading-highlight">dat…</span></h2>
                    <p class="mt-2 opacity-90">Extra info &amp; mogelijkheden.</p>
                </div>

                <div class="p-6 space-y-4 leading-relaxed">
                    <p>…wij ook nagel- en wimpercursussen verzorgen voor groepen en individueel? <span class="font-semibold">Prijzen op aanvraag.</span></p>
                    <p>…alle behandelingen ook mogelijk zijn voor bruiloften en andere speciale gelegenheden? <span class="font-semibold">Prijzen op aanvraag.</span></p>
                    <p>…wij cadeaubonnen verkopen te gebruiken voor alle behandelingen? <span class="font-semibold">Leuk om te geven en om te krijgen!</span></p>
                    <p>…alle behandelingen voor dames en heren zijn en we ook kinderen knippen?</p>
                    <p>…alle prijzen vermeld staan in euro?</p>
                </div>
            </article>

            <article class="rounded-2xl bg-white shadow-card overflow-hidden">
                <div class="p-6 border-b border-black/5">
                    <h2 class="font-serif text-3xl">Openings<span class="heading-highlight">tijden</span></h2>
                    <p class="mt-2 opacity-90">Kom langs of plan je afspraak online.</p>
                </div>

                <div class="p-6">
                    <div class="rounded-2xl bg-brandBg p-5 border border-black/5">
                        <div class="flex items-center justify-between py-2 border-b border-black/10">
                            <div class="font-semibold">Maandag</div><div class="font-extrabold">10.00 – 18.00</div>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-black/10">
                            <div class="font-semibold">Dinsdag</div><div class="font-extrabold">10.00 – 18.00</div>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-black/10">
                            <div class="font-semibold">Woensdag</div><div class="font-extrabold">10.00 – 18.00</div>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-black/10">
                            <div class="font-semibold">Donderdag</div><div class="font-extrabold">10.00 – 21.00</div>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-black/10">
                            <div class="font-semibold">Vrijdag</div><div class="font-extrabold">10.00 – 18.00</div>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-black/10">
                            <div class="font-semibold">Zaterdag</div><div class="font-extrabold">10.00 – 18.00</div>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <div class="font-semibold">Zondag</div><div class="font-extrabold">Op afspraak</div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col sm:flex-row gap-3">
                        <a class="btn-primary w-full sm:w-auto" href="https://salonkee.nl/salon/milas-beauty">Afspraak maken</a>
                        <a class="btn-primary w-full sm:w-auto" href="#contact">Contact</a>
                    </div>

                    <p class="mt-4 text-xs opacity-70">
                        Prijswijzigingen en zetfouten voorbehouden.
                    </p>
                </div>
            </article>

        </div>
    </div>
</section>

<!-- CONTACT -->
<section id="pricing-contact" class="section-pricing-contact pb-16">
    <div class="mx-auto max-w-container px-4">
        <div class="rounded-2xl bg-white shadow-card overflow-hidden">
            <div class="p-6 border-b border-black/5">
                <h2 class="font-serif text-3xl">Contact &amp; <span class="heading-highlight">Locatie</span></h2>
                <p class="mt-2 opacity-90">Bel, mail of stuur een bericht via socials.</p>
            </div>

            <div class="p-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <span aria-hidden="true">📞</span>
                        <a class="font-semibold hover:opacity-80" href="tel:+31610702585">06 10 70 25 85</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <span aria-hidden="true">📞</span>
                        <a class="font-semibold hover:opacity-80" href="tel:+31639200724">06 39 20 07 24</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <span aria-hidden="true">✉️</span>
                        <a class="font-semibold hover:opacity-80" href="mailto:Milasbeauty2018@gmail.com">Milasbeauty2018@gmail.com</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <span aria-hidden="true">📍</span>
                        <div class="font-semibold">Spui 285, 2511 BR Den Haag</div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="pill hover:opacity-80" href="https://www.instagram.com/milasysujeirynails/" target="_blank" rel="noopener">Instagram</a>
                        <a class="pill hover:opacity-80" href="https://salonkee.nl/salon/milas-beauty" target="_blank" rel="noopener">Salonkee</a>
                    </div>
                </div>

                <div class="rounded-2xl bg-brandBg p-6 border border-black/5">
                    <h3 class="font-serif text-xl text-brandPink">Tip</h3>
                    <p class="mt-2 text-sm leading-relaxed">
                        Gebruik de zoekbalk bovenaan om snel je behandeling te vinden (bijv. “wax”, “knippen”, “microblading”).
                        Kom je er niet uit? Stuur ons gerust een bericht.
                    </p>
                    <div class="mt-4">
                        <a class="btn-primary w-full text-center" href="https://salonkee.nl/salon/milas-beauty">Direct afspraak maken</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

<!-- SCRIPTS -->
<script>
    // Mobile menu toggle
    const mobileBtn = document.getElementById("mobileBtn");
    const mobileMenu = document.getElementById("mobileMenu");
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener("click", () => {
            mobileMenu.classList.toggle("hidden");
        });
    }

    // Search in prices
    const input = document.getElementById("priceSearch");
    const clearBtn = document.getElementById("clearSearch");
    const noResults = document.getElementById("noResults");

    const items = Array.from(document.querySelectorAll(".price-item"));
    const sections = Array.from(document.querySelectorAll(".price-section"));

    function normalize(str) {
        return (str || "")
            .toLowerCase()
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // accents
            .replace(/\s+/g, " ")
            .trim();
    }

    function applySearch(qRaw) {
        const q = normalize(qRaw);

        let visibleCount = 0;

        // filter items
        items.forEach((el) => {
            const key = normalize(el.dataset.name || "");
            const text = normalize(el.innerText || "");
            const match = !q || key.includes(q) || text.includes(q);

            el.classList.toggle("hidden-by-search", !match);
            if (match) visibleCount++;
        });

        // hide sections with 0 visible items
        sections.forEach((sec) => {
            const hasVisible = !!sec.querySelector(".price-item:not(.hidden-by-search)");
            sec.classList.toggle("hidden-by-search", !hasVisible && !!q);
        });

        if (noResults) {
            noResults.classList.toggle("hidden", visibleCount !== 0 || !q);
        }
    }

    if (input) {
        input.addEventListener("input", (e) => applySearch(e.target.value));
    }
    if (clearBtn && input) {
        clearBtn.addEventListener("click", () => {
            input.value = "";
            applySearch("");
            input.focus();
        });
    }
</script>

</body>
</html>

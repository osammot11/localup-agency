<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Local Up aiuta le attività locali in Toscana a farsi scegliere con social media marketing e siti web curati su misura.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Local Up | Marketing digitale per attività locali">
    <meta property="og:description" content="Social media marketing e siti web per le attività locali in Toscana.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="facebook-domain-verification" content="q5ql1uy3jhtg0uopl12bgc3a00z5ba" />
    <title>Local Up | Marketing digitale per attività locali</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body class="home-page">
    <a class="home-skip" href="#contenuto">Vai al contenuto</a>
    <header class="home-header">
        <div class="home-shell home-header-inner">
            <a href="{{ url('/') }}" aria-label="Local Up, homepage" class="home-logo"><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up" width="180" height="40"></a>
            <nav class="home-nav" aria-label="Navigazione principale">
                <a href="#soluzioni">Le soluzioni</a>
                <a class="home-nav-contact" href="https://wa.me/393208908274" target="_blank" rel="noopener noreferrer">Parliamone <span aria-hidden="true">↗</span></a>
            </nav>
        </div>
    </header>

    <main id="contenuto">
        <section class="home-hero" aria-labelledby="home-title">
            <div class="home-shell home-hero-grid">
                <div class="home-hero-copy">
                    <p class="home-kicker"><span class="home-kicker-dot" aria-hidden="true"></span> Marketing digitale, fatto in Toscana</p>
                    <h1 id="home-title">La tua attività merita di <span>farsi notare.</span></h1>
                    <p class="home-lead">Aiutiamo le attività locali a raccontarsi meglio, farsi trovare dalle persone giuste e trasformare l’attenzione in nuovi contatti.</p>
                    <div class="home-hero-actions">
                        <a class="home-primary" href="#soluzioni">Scopri le soluzioni <span aria-hidden="true">↗</span></a>
                        <a class="home-text-link" href="https://wa.me/393208908274" target="_blank" rel="noopener noreferrer">Scrivici su WhatsApp <span aria-hidden="true">↗</span></a>
                    </div>
                    <p class="home-hero-note">Per chi lavora ogni giorno sul territorio. Come noi.</p>
                </div>
                <div class="home-art" aria-hidden="true">
                    <div class="home-art-ring home-art-ring-one"></div>
                    <div class="home-art-ring home-art-ring-two"></div>
                    <div class="home-art-center"><span>LOCAL</span><strong>UP<span class="home-art-dot">.</span></strong></div>
                    <div class="home-art-tag home-art-tag-top">Strategia</div>
                    <div class="home-art-tag home-art-tag-bottom">Creatività <span>↗</span></div>
                    <div class="home-art-corner">LU / 01</div>
                </div>
            </div>
        </section>

        <section id="soluzioni" class="home-services" aria-labelledby="services-title">
            <div class="home-shell">
                <div class="home-section-heading">
                    <div><p class="home-section-kicker">01 / Quello che facciamo</p><h2 id="services-title">La soluzione giusta <em>per la tua attività.</em></h2></div>
                    <p>Ogni attività ha una storia diversa. Scegli il percorso più vicino a te e scopri come possiamo lavorare insieme.</p>
                </div>

                <div class="home-services-grid">
                    <a class="home-service-card home-service-featured" href="{{ route('sites.landing') }}">
                        <span class="home-service-top"><span>01 / Presenza online</span><span class="home-service-arrow" aria-hidden="true">↗</span></span>
                        <span class="home-service-body"><span class="home-service-icon" aria-hidden="true">▣</span><strong>Siti web che parlano della tua attività.</strong><span>Una presenza digitale chiara, curata e facile da usare. Dalla prima visita al primo contatto.</span></span>
                        <span class="home-service-bottom">Scopri i siti web <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="home-service-card" href="{{ route('restaurants.landing') }}">
                        <span class="home-service-top"><span>02 / Social media</span><span class="home-service-arrow" aria-hidden="true">↗</span></span>
                        <span class="home-service-body"><span class="home-service-icon" aria-hidden="true">◈</span><strong>Ristoranti</strong><span>Contenuti e campagne pensati per far venire voglia di sedersi al tuo tavolo.</span></span>
                        <span class="home-service-bottom">Scopri il percorso <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="home-service-card" href="{{ route('beauty.landing') }}">
                        <span class="home-service-top"><span>03 / Social media</span><span class="home-service-arrow" aria-hidden="true">↗</span></span>
                        <span class="home-service-body"><span class="home-service-icon" aria-hidden="true">✳</span><strong>Beauty</strong><span>Racconta la cura, lo stile e le persone dietro il tuo salone o centro estetico.</span></span>
                        <span class="home-service-bottom">Scopri il percorso <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="home-service-card" href="{{ route('antiques.landing') }}">
                        <span class="home-service-top"><span>04 / Social media</span><span class="home-service-arrow" aria-hidden="true">↗</span></span>
                        <span class="home-service-body"><span class="home-service-icon" aria-hidden="true">✦</span><strong>Antiquariato</strong><span>Dai spazio al valore degli oggetti, alla loro storia e a ciò che li rende unici.</span></span>
                        <span class="home-service-bottom">Scopri il percorso <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="home-service-card" href="{{ route('pastry.landing') }}">
                        <span class="home-service-top"><span>05 / Social media</span><span class="home-service-arrow" aria-hidden="true">↗</span></span>
                        <span class="home-service-body"><span class="home-service-icon" aria-hidden="true">✳</span><strong>Pasticcerie</strong><span>Fai vedere il lavoro, i dettagli e i dolci che meritano di essere scelti.</span></span>
                        <span class="home-service-bottom">Scopri il percorso <span aria-hidden="true">→</span></span>
                    </a>
                </div>
            </div>
        </section>

        <section class="home-contact" aria-labelledby="contact-title">
            <div class="home-shell home-contact-inner">
                <div><p class="home-section-kicker">02 / Facciamo due chiacchiere</p><h2 id="contact-title">La prossima storia da raccontare potrebbe essere <em>la tua.</em></h2></div>
                <a class="home-contact-button" href="https://wa.me/393208908274" target="_blank" rel="noopener noreferrer">Scrivici su WhatsApp <span aria-hidden="true">↗</span></a>
            </div>
        </section>
    </main>

    <footer class="home-footer">
        <div class="home-shell home-footer-main">
            <div><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up" width="144" height="32"><p>Marketing digitale per attività locali in Toscana.</p></div>
            <div class="home-footer-links"><a href="mailto:localupitalia@gmail.com">localupitalia@gmail.com</a><a href="https://wa.me/393208908274" target="_blank" rel="noopener noreferrer">WhatsApp +39 320 890 8274</a></div>
        </div>
        <div class="home-shell home-footer-bottom">
            <span>© {{ date('Y') }} Local Up · P.IVA 02727380467</span>
            <nav aria-label="Link legali"><a href="https://localupitalia.com/privacy-policy">Privacy</a><a href="https://localupitalia.com/cookie-policy">Cookie</a><a href="https://localupitalia.com/termini-e-condizioni">Termini</a>@unless(config('services.meta.track_without_consent'))<button type="button" class="cookie-preferences-open">Preferenze cookie</button>@endunless</nav>
        </div>
    </footer>
    @include('partials.meta-tracking')
</body>
</html>

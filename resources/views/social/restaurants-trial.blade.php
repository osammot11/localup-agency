<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Un mese di gestione social gratuita per ristoranti in Toscana: foto, video, testi e pubblicazione. Raccontaci del tuo locale e verifica la disponibilità.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Un mese di social gratuito per il tuo ristorante | Local Up">
    <meta property="og:description" content="Veniamo nel tuo locale, creiamo i contenuti e li pubblichiamo. Il primo mese di gestione è gratuito, poi decidi tu.">
    <meta property="og:url" content="{{ route('restaurants-trial.landing') }}">
    <meta name="facebook-domain-verification" content="q5ql1uy3jhtg0uopl12bgc3a00z5ba">
    <title>Mese gratuito per ristoranti in Toscana | Local Up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/restaurants-trial.css') }}">
    <script src="{{ asset('js/restaurants-trial.js') }}" defer></script>
</head>
<body class="trial-page" data-submitted-form="{{ old('_form_placement') }}">
    <a class="trial-skip" href="#contenuto">Vai al contenuto</a>
    <header class="trial-header">
        <div class="trial-wrap trial-header-inner">
            <a href="{{ route('restaurants-trial.landing') }}" class="trial-logo" aria-label="Local Up, torna all'inizio della pagina">
                <img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up">
            </a>
            <a class="trial-header-link" href="#modulo-hero">Richiedi il mese gratuito <span aria-hidden="true">↗</span></a>
        </div>
    </header>

    <main id="contenuto">
        <section class="trial-hero" aria-labelledby="trial-title">
            <div class="trial-wrap trial-hero-grid">
                <div class="trial-hero-copy">
                    <h1 id="trial-title">Non gestiamo solo i social media.<em>Riempiamo il tuo ristorante.</em></h1>
                    <p class="trial-lead">In 30 giorni rivoluzioneremo il modo in cui vengono gestiti i social del tuo ristorante, non per attirare like e visualizzazioni, ma per riempire i tavoli. Ogni singolo giorno (sì anche il martedì sera).</p>
                    <p class="trial-promise">30 giorni 100% gratuiti e senza impegno. <strong>Continuiamo solo se otteniamo risultati.</strong></p>
                    <div id="modulo-hero" class="trial-hero-inline-form" role="region" aria-label="Modulo di richiesta">
                        <p class="trial-under-cta">Compila una breve richiesta. Ti ricontattiamo per conoscere il ristorante, verificare la disponibilità e spiegarti come funziona la prova.</p>
                        @include('social.partials.trial-restaurants-form', ['placement' => 'hero'])
                    </div>
                </div>
                <div class="trial-hero-art" aria-hidden="true">
                    <div class="trial-art-orbit trial-art-orbit-one"></div>
                    <div class="trial-art-orbit trial-art-orbit-two"></div>
                    <div class="trial-art-plate"><span>01</span><strong>mese</strong><small>per farti vedere<br>come meriti</small></div>
                    <div class="trial-art-note trial-art-note-top">Foto + video</div>
                    <div class="trial-art-note trial-art-note-bottom">Creati nel tuo locale</div>
                    <p class="trial-art-caption">IL PRIMO MESE<br><strong>LO OFFRIAMO NOI.</strong></p>
                </div>
            </div>
           <div class="trial-wrap trial-hero-foot"><span>Strategia</span><span>Contenuti</span><span>Pubblicazione</span><span>Un mese per provarci</span></div>
        </section>
{{--
        <section class="trial-section trial-intro" aria-labelledby="trial-seen-title">
            <div class="trial-wrap trial-intro-grid">
                <div><p class="trial-kicker">Il punto di partenza</p><h2 id="trial-seen-title">Quello che servi merita di <em>essere visto.</em></h2></div>
                <div class="trial-prose"><p>Un cliente non può assaggiare un piatto dal telefono. Può però vedere una cucina viva, una sala accogliente e un piatto che gli fa pensare: <strong>«Qui voglio andare».</strong></p><p>Se il tuo profilo è fermo o mostra poco di ciò che rende speciale il locale, gli stai rendendo difficile sceglierti. Noi ci occupiamo dei contenuti mentre tu ti occupi dei clienti.</p></div>
            </div>
        </section>

        <section class="trial-section trial-process" aria-labelledby="trial-work-title">
            <div class="trial-wrap">
                <p class="trial-kicker">Un mese, tutto il lavoro necessario</p>
                <h2 id="trial-work-title">Cosa facciamo durante<br>il <em>mese gratuito.</em></h2>
                <div class="trial-cards">
                    <article class="trial-card"><span class="trial-card-number">01 / SUL POSTO</span><h3>Veniamo nel tuo ristorante</h3><p>Conosciamo il locale, i piatti e le persone che vuoi raggiungere. Organizziamo foto e riprese senza intralciare il servizio.</p></article>
                    <article class="trial-card"><span class="trial-card-number">02 / IN STUDIO</span><h3>Prepariamo i contenuti</h3><p>Selezioniamo le immagini, montiamo i video e scriviamo testi che raccontano perché vale la pena venire da te.</p></article>
                    <article class="trial-card"><span class="trial-card-number">03 / SUI SOCIAL</span><h3>Li pubblichiamo sul tuo profilo</h3><p>Concordiamo un piano per il mese e gestiamo la pubblicazione. Tu sai cosa esce e quando.</p></article>
                </div>
                <a class="trial-button trial-button-light" href="#modulo">Candida il tuo ristorante <span aria-hidden="true">↗</span></a>
            </div>
        </section>

        <section class="trial-section trial-example" aria-labelledby="trial-example-title">
            <div class="trial-wrap trial-example-grid">
                <div class="trial-example-visual"><div class="trial-example-image"><img src="{{ asset('images/restaurants/focolento.png') }}" alt="Logo del Chiosco Focolento" loading="lazy"></div><span class="trial-example-stamp">Un esempio reale<br>del nostro lavoro</span></div>
                <div><p class="trial-kicker">Guarda con i tuoi occhi</p><h2 id="trial-example-title">Un profilo racconta <em>più di mille promesse.</em></h2><p class="trial-example-name">Chiosco Focolento</p><p>Abbiamo lavorato ai contenuti del suo profilo Instagram. Guarda direttamente foto, video e modo di raccontare il locale: è il modo più semplice per valutare il nostro lavoro.</p><a class="trial-text-link" href="https://www.instagram.com/chioscofocolento/" target="_blank" rel="noopener noreferrer">Guarda il profilo Instagram <span aria-hidden="true">↗</span></a><p class="trial-example-note">Il lavoro sul tuo ristorante sarà costruito sui tuoi piatti, sul tuo locale e sui tuoi obiettivi.</p></div>
            </div>
        </section>

        <section class="trial-section trial-cost" aria-labelledby="trial-cost-title">
            <div class="trial-wrap trial-cost-grid">
                <div><p class="trial-kicker">Chiaro fin dall'inizio</p><h2 id="trial-cost-title">Il primo mese:<br><em>cosa paghi?</em></h2><div class="trial-zero" aria-label="La nostra gestione costa zero euro il primo mese">€ 0 <span>per la nostra gestione<br>nel primo mese</span></div></div>
                <div class="trial-cost-copy"><p><strong>La nostra gestione è gratuita per il primo mese.</strong> Comprende la preparazione del piano, la creazione dei contenuti, il montaggio, i testi e la pubblicazione concordata.</p><p>Non hai l’obbligo di continuare dopo la prova. Al termine del mese valuti il lavoro svolto e decidi se proseguire.</p><div class="trial-cost-note"><strong>Le campagne sono una scelta separata.</strong><p>Se scegliamo insieme di fare anche campagne pubblicitarie, il budget degli annunci è separato: lo decidi tu e lo paghi direttamente a Meta. Non è necessario autorizzare una spesa pubblicitaria per inviare questa richiesta.</p></div><a class="trial-button" href="#modulo">Richiedi il mese gratuito <span aria-hidden="true">↗</span></a></div>
            </div>
        </section>

        <section class="trial-section trial-start" aria-labelledby="trial-start-title">
            <div class="trial-wrap"><p class="trial-kicker">Semplice, dall'inizio</p><h2 id="trial-start-title">Come si comincia.</h2><ol class="trial-start-list"><li><span>01</span><div><h3>Ci racconti del tuo ristorante</h3><p>Compili il breve modulo qui sotto.</p></div></li><li><span>02</span><div><h3>Parliamo con te</h3><p>Ti ricontattiamo per capire di cosa ha bisogno il locale, rispondere alle tue domande e verificare quando possiamo iniziare.</p></div></li><li><span>03</span><div><h3>Concordiamo il mese di prova</h3><p>Definiamo insieme contenuti e attività. Solo dopo la tua approvazione partiamo con il lavoro.</p></div></li></ol></div>
        </section>



        <section class="trial-section trial-form-section" id="modulo" aria-labelledby="trial-form-title">
            <div class="trial-wrap trial-form-grid">
                <div class="trial-form-side"><p class="trial-kicker">La tua richiesta</p><h2 id="trial-form-title">Raccontaci del tuo <em>ristorante.</em></h2><p>La richiesta richiede meno di un minuto. Non avvia automaticamente il mese di prova: prima parlerai con il nostro team.</p><div class="trial-form-side-note"><span aria-hidden="true">↗</span><p>Ti ricontattiamo entro 24 ore. Nessun impegno a proseguire dopo il mese gratuito.</p></div></div>
                @include('social.partials.trial-restaurants-form', ['placement' => 'bottom'])
            </div>
        </section>

        --}}

        <section class="trial-section trial-faq" aria-labelledby="trial-faq-title"><div class="trial-wrap trial-faq-grid"><div><p class="trial-kicker">Risposte chiare</p><h2 id="trial-faq-title">Domande <em>frequenti.</em></h2></div><div class="trial-faq-list">
            <details><summary>Il mese è davvero gratuito?</summary><p>Sì. La gestione descritta in questa pagina è gratuita per il primo mese. Se concordiamo campagne pubblicitarie, il budget degli annunci è una spesa separata che decidi tu e paghi direttamente a Meta.</p></details>
            <details><summary>Devo decidere ora se continuare dopo il mese di prova?</summary><p>No. Prima svolgiamo il mese concordato; poi valuti il lavoro e decidi se proseguire.</p></details>
            <details><summary>Devo creare io foto, video e testi?</summary><p>No. Veniamo nel locale per creare i contenuti e ci occupiamo di montaggio, testi e pubblicazione. Concordiamo con te cosa raccontare e quando realizzare le riprese.</p></details>
            <details><summary>Lavorate anche nel mio comune?</summary><p>Seguiamo ristoranti in Toscana. Dopo la richiesta verifichiamo insieme la disponibilità per il tuo locale.</p></details>
         {{--   <details><summary>Quando inizia il mese gratuito?</summary><p>Dopo aver parlato con te, verifichiamo la disponibilità e concordiamo la data di partenza. L’invio del modulo non attiva automaticamente il servizio.</p></details> --}}
        </div></div></section>

        <section class="trial-final" aria-labelledby="trial-final-title"><div class="trial-wrap trial-final-inner"><p class="trial-kicker">Ci vediamo nel tuo locale?</p><h2 id="trial-final-title">Il tuo prossimo cliente potrebbe già stare guardando <em>Instagram.</em></h2><p>Facciamo vedere ciò che rende desiderabile il tuo ristorante. Raccontaci del locale e valutiamo insieme il mese gratuito.</p><a class="trial-button trial-button-light" href="#modulo-hero">Richiedi il mese gratuito <span aria-hidden="true">↗</span></a></div></section>
    </main>

    <footer class="trial-footer"><div class="trial-wrap trial-footer-inner"><div><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up"><p>Marketing digitale per attività locali in Toscana.</p></div><nav aria-label="Link legali"><a href="https://localupitalia.com/privacy-policy">Privacy</a><a href="https://localupitalia.com/cookie-policy">Cookie</a><a href="https://localupitalia.com/termini-e-condizioni">Termini</a>@unless(config('services.meta.track_without_consent'))<button type="button" class="cookie-preferences-open">Preferenze cookie</button>@endunless</nav><span>© {{ date('Y') }} Local Up · P.IVA 02727380467</span></div></footer>
    @include('partials.meta-tracking')
</body>
</html>

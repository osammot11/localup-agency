<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Un mese di gestione social gratuita per saloni e centri estetici in Toscana. Raccontaci della tua attività e verifica la disponibilità.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Mese gratuito per saloni e centri estetici | Local Up">
    <meta property="og:description" content="Un mese di gestione social gratuita per saloni e centri estetici in Toscana. Raccontaci della tua attività e verifica la disponibilità.">
    <meta property="og:url" content="{{ route('beauty-trial.landing') }}">
    <meta name="facebook-domain-verification" content="q5ql1uy3jhtg0uopl12bgc3a00z5ba">
    <title>Mese gratuito per saloni e centri estetici | Local Up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/restaurants-trial.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trial-variants.css') }}">
    <script src="{{ asset('js/restaurants-trial.js') }}" defer></script>
</head>
<body class="trial-page trial-variant trial-variant-beauty" data-submitted-form="{{ old('_form_placement') }}">
    <a class="trial-skip" href="#contenuto">Vai al contenuto</a>
    <header class="trial-header"><div class="trial-wrap trial-header-inner">
        <a href="{{ route('beauty-trial.landing') }}" class="trial-logo" aria-label="Local Up, torna all'inizio della pagina"><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up"></a>
        <a class="trial-header-link" href="#modulo">Richiedi il mese gratuito <span aria-hidden="true">↗</span></a>
    </div></header>

    <main id="contenuto">
        <section class="trial-hero" aria-labelledby="trial-title"><div class="trial-wrap trial-hero-grid">
            <div class="trial-hero-copy">
                <h1 id="trial-title">Il calendario appuntamenti del tuo salone deve essere pieno. <em>Questo è ciò che facciamo.</em></h1>
                <p class="trial-lead">Basta con attività inutili che portano solo commenti e like, mentre le tue potenziali clienti continuano a scegliere un altro salone. Gestiamo i toui social completamente gratis per 30 giorni, se i risultati non arrivano, non ci paghi. </p>
                <p class="trial-promise">Gestione completa per i primi 30 giorni 100% gratis. <strong>Poi decidi tu se continuare, senza alcun obbligo.</strong></p>
                <div id="modulo-hero" class="trial-hero-inline-form" role="region" aria-label="Modulo di richiesta">
                    <p class="trial-under-cta">Compila una breve richiesta. Ti ricontattiamo per conoscere la tua attività, verificare la disponibilità e spiegarti come funziona la prova.</p>
                    @include('social.partials.beauty-trial-form', ['placement' => 'hero'])
                </div>
            </div>
            <div>
                <img src="/images/beauty/parrucchiera.jpg" style="width: 100%;">
            </div>
        {{--
        </div><div class="trial-wrap trial-hero-foot"><span>Strategia</span><span>Foto e video</span><span>Pubblicazione</span><span>Un mese per provarci</span></div></section>

        <section class="trial-section trial-intro" aria-labelledby="trial-intro-title"><div class="trial-wrap trial-intro-grid">
            <div><p class="trial-kicker">Il punto di partenza</p><h2 id="trial-intro-title">Il tuo talento merita di <em>essere visto.</em></h2></div>
            <div class="trial-prose"><p>Le persone scelgono a chi affidarsi anche da quello che vedono sul telefono. Foto recenti, risultati reali e un profilo curato aiutano a capire subito la qualità del tuo lavoro.</p><p>Se il profilo è fermo o racconta poco della tua attività, rendi più difficile sceglierti. Noi ci occupiamo dei contenuti mentre tu ti occupi dei clienti.</p></div>
        </div></section>

        <section class="trial-section trial-process" aria-labelledby="trial-process-title"><div class="trial-wrap">
            <p class="trial-kicker">Un mese, tutto il lavoro necessario</p><h2 id="trial-process-title">Cosa facciamo durante il <em>mese gratuito.</em></h2>
            <div class="trial-cards"><article class="trial-card"><span class="trial-card-number">01 / SUL POSTO</span><h3>Veniamo nella tua attività</h3><p>Conosciamo i tuoi servizi, i tuoi lavori e le persone che vuoi raggiungere. Organizziamo foto e riprese senza intralciare gli appuntamenti.</p></article><article class="trial-card"><span class="trial-card-number">02 / IN STUDIO</span><h3>Prepariamo i contenuti</h3><p>Selezioniamo le immagini, montiamo i video e scriviamo testi che mostrano la qualità del tuo lavoro.</p></article><article class="trial-card"><span class="trial-card-number">03 / SUI SOCIAL</span><h3>Li pubblichiamo sul tuo profilo</h3><p>Concordiamo il piano del mese e gestiamo la pubblicazione. Tu sai cosa esce e quando.</p></article></div>
            <a class="trial-button trial-button-light" href="#modulo">Candida la tua attività <span aria-hidden="true">↗</span></a>
        </div></section>

        <section class="trial-section trial-example" aria-labelledby="trial-example-title"><div class="trial-wrap trial-example-grid">
            <div class="trial-example-visual"><div class="trial-example-image"><img src="{{ asset('images/beauty/piegaia.png') }}" alt="Piegaia Parrucchieri" loading="lazy"></div><span class="trial-example-stamp">Un esempio reale<br>del nostro lavoro</span></div>
            <div><p class="trial-kicker">Guarda con i tuoi occhi</p><h2 id="trial-example-title">Un profilo racconta <em>più di mille promesse.</em></h2><p class="trial-example-name">Piegaia Parrucchieri</p><p>Abbiamo lavorato ai contenuti del profilo Instagram di Piegaia Parrucchieri. Guarda direttamente foto, video e modo di raccontare il salone.</p><a class="trial-text-link" href="https://www.instagram.com/piegaia_parrucchieri/" target="_blank" rel="noopener noreferrer">Guarda il profilo Instagram <span aria-hidden="true">↗</span></a><p class="trial-example-note">Il lavoro sulla tua attività sarà costruito sui tuoi servizi, sui tuoi clienti e sui tuoi obiettivi.</p></div>
        </div></section>

        <section class="trial-section trial-cost" aria-labelledby="trial-offer-title"><div class="trial-wrap trial-cost-grid">
            <div><p class="trial-kicker">Chiaro fin dall’inizio</p><h2 id="trial-offer-title">Il primo mese:<br><em>cosa paghi?</em></h2><div class="trial-zero">€ 0 <span>per la nostra gestione<br>nel primo mese</span></div></div>
            <div class="trial-cost-copy"><p>La nostra gestione è gratuita per il primo mese. Comprende il piano, la creazione dei contenuti, il montaggio, i testi e la pubblicazione concordata.</p><p>Al termine del mese valuti il lavoro svolto e decidi se proseguire. Non hai l’obbligo di continuare.</p><div class="trial-cost-note"><strong>Le campagne sono una scelta separata.</strong><p>Se concordiamo campagne pubblicitarie, il budget degli annunci è separato: lo decidi tu e lo paghi direttamente a Meta.</p></div><a class="trial-button" href="#modulo">Richiedi il mese gratuito <span aria-hidden="true">↗</span></a></div>
        </div></section>

        <section class="trial-section trial-start" aria-labelledby="trial-start-title"><div class="trial-wrap"><p class="trial-kicker">Semplice, dall'inizio</p><h2 id="trial-start-title">Come si comincia.</h2><ol class="trial-start-list"><li><span>01</span><div><h3>Ci racconti della tua attività</h3><p>Compili il breve modulo qui sotto.</p></div></li><li><span>02</span><div><h3>Parliamo con te</h3><p>Ti ricontattiamo per capire i tuoi servizi, rispondere alle domande e verificare la disponibilità.</p></div></li><li><span>03</span><div><h3>Concordiamo il mese di prova</h3><p>Definiamo insieme contenuti e attività. Partiamo solo dopo la tua approvazione.</p></div></li></ol></div></section>

        <section class="trial-section trial-form-section" id="modulo" aria-labelledby="trial-form-title"><div class="trial-wrap trial-form-grid">
            <div class="trial-form-side"><p class="trial-kicker">La tua richiesta</p><h2 id="trial-form-title">Raccontaci della tua <em>attività.</em></h2><p>La richiesta richiede meno di un minuto. Prima di iniziare il mese di prova parlerai con il nostro team.</p><div class="trial-form-side-note"><span aria-hidden="true">↗</span><p>Ti ricontattiamo entro 24 ore. Nessun impegno.</p></div></div>
            @include('social.partials.beauty-trial-form', ['placement' => 'bottom'])
        </div></section>



        <section class="trial-section trial-faq" aria-labelledby="trial-faq-title"><div class="trial-wrap trial-faq-grid"><div><p class="trial-kicker">Risposte chiare</p><h2 id="trial-faq-title">Domande <em>frequenti.</em></h2></div><div class="trial-faq-list"><details><summary>Il mese è davvero gratuito?</summary><p>Sì. La nostra gestione descritta in questa pagina è gratuita per il primo mese. L’eventuale budget delle campagne è separato e lo paghi direttamente a Meta.</p></details><details><summary>Devo decidere ora se continuare?</summary><p>No. Al termine del mese valuti il lavoro e decidi se proseguire.</p></details><details><summary>Devo creare io foto e video?</summary><p>No. Veniamo nella tua attività, realizziamo i contenuti e ci occupiamo di montaggio, testi e pubblicazione.</p></details>
           {{-- <details><summary>Lavorate anche nel mio comune?</summary><p>Seguiamo attività in Toscana. Dopo la richiesta verifichiamo la disponibilità per la tua zona.</p></details>  </div></div></section>
        <section class="trial-final" aria-labelledby="trial-final-title"><div class="trial-wrap trial-final-inner"><p class="trial-kicker">Facciamo vedere il tuo lavoro?</p><h2 id="trial-final-title">La prossima cliente potrebbe già stare guardando <em>Instagram.</em></h2><p>Raccontaci della tua attività e valutiamo insieme il mese gratuito.</p><a class="trial-button trial-button-light" href="#modulo">Richiedi il mese gratuito <span aria-hidden="true">↗</span></a></div></section>
--}}
        </main>

    <footer class="trial-footer"><div class="trial-wrap trial-footer-inner"><div><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up"><p>Marketing digitale per attività locali in Toscana.</p></div><nav aria-label="Link legali"><a href="https://localupitalia.com/privacy-policy">Privacy</a><a href="https://localupitalia.com/cookie-policy">Cookie</a><a href="https://localupitalia.com/termini-e-condizioni">Termini</a>@unless(config('services.meta.track_without_consent'))<button type="button" class="cookie-preferences-open">Preferenze cookie</button>@endunless</nav><span>© {{ date('Y') }} Local Up · P.IVA 02727380467</span></div></footer>
    @include('partials.meta-tracking')
</body>
</html>

<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Veniamo nel tuo laboratorio, riprendiamo i tuoi dolci e pubblichiamo noi. Il primo mese di gestione social per la tua pasticceria costa € 0.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Il primo mese è gratis | Local Up per pasticcerie">
    <meta property="og:description" content="Veniamo nel tuo laboratorio, riprendiamo i tuoi dolci e pubblichiamo noi. Il primo mese di gestione social per la tua pasticceria costa € 0.">
    <meta property="og:url" content="{{ route('pastry-v3.landing') }}">
    <meta name="facebook-domain-verification" content="q5ql1uy3jhtg0uopl12bgc3a00z5ba">
    <title>Il primo mese è gratis | Local Up per pasticcerie</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/restaurants-trial.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trial-variants.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pastry-v3.css') }}">
    <script src="{{ asset('js/restaurants-trial.js') }}" defer></script>
</head>
<body class="trial-page trial-variant trial-variant-pastry pv3" data-submitted-form="{{ old('_form_placement') }}">
    <a class="trial-skip" href="#contenuto">Vai al contenuto</a>
    <p class="pv3-proofbar">Pasticceria Claudio, Lucca: <strong>1.239.531 visualizzazioni in 90 giorni</strong> con i contenuti Local Up · <a href="#prova">Guarda i dati</a></p>
    <header class="pv3-header"><div class="pv3-wrap pv3-header-inner">
        <a href="{{ route('pastry-v3.landing') }}" class="pv3-logo" aria-label="Local Up, torna all'inizio della pagina"><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up"></a>
        <a class="pv3-header-cta" href="#modulo-hero">Voglio il mese gratuito</a>
    </div></header>

    <main id="contenuto">
        <section class="pv3-hero" aria-labelledby="pv3-title"><div class="pv3-wrap pv3-hero-grid">
            <div class="pv3-hero-copy">
                <p class="pv3-eyebrow" data-sales-step="1">Per chi ha una pasticceria e alle sei è già in laboratorio</p>
                <h1 id="pv3-title" data-sales-step="2">Trasformiamo quello che sforni ogni mattina in video che fanno venire fame a chi abita vicino a te. <em>In 30 giorni, senza che tu tocchi il telefono.</em></h1>
                <p class="pv3-sub" data-sales-step="3">Con il <strong>Metodo Appena Sfornato</strong> veniamo noi nel tuo laboratorio, riprendiamo crema, sfoglia e vassoi nel momento in cui fanno più gola, poi montiamo, scriviamo e pubblichiamo. <strong>La nostra gestione del primo mese costa € 0.</strong> Alla fine decidi tu se continuare.</p>
                <div class="pv3-hero-proof">
                    <img src="{{ asset('images/pasticcerie/claudio.png') }}" alt="Pasticceria Claudio" width="120" height="44">
                    <p><strong>1.239.531 visualizzazioni</strong> e <strong>+2.988 follower</strong> in 90 giorni per Pasticceria Claudio. Dati Instagram, li trovi più sotto.</p>
                </div>
            </div>
            <div class="pv3-form-shell" id="modulo-hero" role="region" aria-label="Modulo di richiesta del mese gratuito">
                <div class="pv3-form-heading"><span>1 minuto, 2 passaggi</span><h2>Richiedi il mese gratuito per la tua pasticceria</h2><p>Ti chiamiamo entro 24 ore per conoscere i tuoi prodotti e verificare la disponibilità nella tua zona.</p></div>
                @include('social.partials.pastry-trial-form', ['placement' => 'hero', 'formRoute' => 'pastry-v3.lead'])
            </div>
        </div></section>

        <section class="pv3-section pv3-intrigue" data-sales-step="4" aria-labelledby="pv3-intrigue-title"><div class="pv3-wrap pv3-narrow">
            <h2 id="pv3-intrigue-title">La pasticceria a tre chilometri da te vende torte che non sono migliori delle tue. <em>E il motivo non sta nella ricetta.</em></h2>
            <p>La gente non ha assaggiato il prodotto migliore. Ha comprato quello che le ha fatto venire voglia per primo.</p>
            <p>Mentre tu sfornavi, lei ha mostrato la crema che cola, il cornetto appena aperto, il vassoio ancora caldo. E chi scorreva Instagram la sera ha deciso lì dove fare colazione domani.</p>
        </div></section>

        <section class="pv3-section pv3-pain" data-sales-step="5" aria-labelledby="pv3-pain-title"><div class="pv3-wrap">
            <h2 id="pv3-pain-title">Alle 18 il vassoio che avanza non è invenduto. <em>È margine buttato nel cestino.</em></h2>
            <div class="pv3-timeline">
                <article><span>05:40</span><h3>Il laboratorio</h3><p>Burro, uova, farina, corrente, ore di lavoro. La giornata è già costata prima di alzare la serranda.</p></article>
                <article><span>08:00</span><h3>La vetrina è piena</h3><p>È il momento in cui i tuoi dolci sono più belli. E non li vede nessuno, a parte chi passa davanti per caso.</p></article>
                <article><span>18:00</span><h3>Quello che resta</h3><p>Non è soltanto invenduto. È lavoro e margine che finiscono nel posto sbagliato.</p></article>
                <article><span>22:30</span><h3>Sul divano</h3><p>Scorri Instagram, vedi i video della concorrenza e pensi: «Domani devo fare qualcosa per i social». Ma quando?</p></article>
            </div>
        </div></section>

        <section class="pv3-section pv3-failed" aria-labelledby="pv3-failed-title"><div class="pv3-wrap">
            <h2 id="pv3-failed-title">Hai già provato a sistemare i social? <em>Ecco perché non ha funzionato. E non è colpa tua.</em></h2>
            <div class="pv3-failed-grid">
                <article><h3>Le foto col cellulare tra un'infornata e l'altra</h3><p>Le fai di corsa, con la luce del laboratorio, quando hai le mani libere. Il dolce sembra piatto, arrivano otto like e tre sono di parenti.</p></article>
                <article><h3>L'agenzia che ti dice «mandaci tu le foto»</h3><p>Paghi ogni mese, ricevi grafiche con «Buon lunedì» e il lavoro vero resta a te. Se i video li devi fare tu, l'agenzia a cosa serve?</p></article>
                <article><h3>Il videomaker che fa un bel video e sparisce</h3><p>Un filmato curato, una volta. Poi nessuno pubblica, nessuno scrive i testi, nessuno ci pensa il mese dopo.</p></article>
                <article><h3>Il ragazzo che «sa usare Instagram»</h3><p>Parte con entusiasmo e dopo tre settimane il profilo è di nuovo fermo. Nessun piano, nessun prodotto da spingere.</p></article>
            </div>
            <p class="pv3-failed-punch">Il problema è sempre lo stesso: <strong>qualcuno deve venire in laboratorio, riprendere i dolci nel momento giusto e pubblicarli con costanza.</strong> Tu non puoi farlo. Noi sì.</p>
        </div></section>

        <section class="pv3-section pv3-mechanism" data-sales-step="6" aria-labelledby="pv3-mechanism-title"><div class="pv3-wrap">
            <p class="pv3-label">Il Metodo Appena Sfornato</p>
            <h2 id="pv3-mechanism-title">Riprendiamo i tuoi dolci nel momento in cui fanno più gola. <em>Poi li mettiamo davanti a chi può entrare.</em></h2>
            <ol class="pv3-steps">
                <li><span>01</span><div><h3>Scegliamo cosa far desiderare</h3><p>Non tutti i dolci pesano uguale sulla cassa. Partiamo dai prodotti che ti lasciano margine e dalle occasioni che riempiono il calendario degli ordini: compleanni, feste, ricorrenze.</p></div></li>
                <li><span>02</span><div><h3>Veniamo noi in laboratorio</h3><p>Riprendiamo la crema che cola, la sfoglia che si apre, il vassoio appena uscito dal forno. Lavoriamo intorno a te, senza fermare la produzione.</p></div></li>
                <li><span>03</span><div><h3>Montiamo, scriviamo, pubblichiamo</h3><p>Video, foto e testi pronti sul tuo profilo. Tu sai cosa esce e quando. Se vuoi spingere sulla tua zona, impostiamo campagne con un budget deciso da te e pagato direttamente a Meta.</p></div></li>
            </ol>
            <p class="pv3-mechanism-close">Tu fai il pasticcere. <strong>Il resto lo facciamo noi.</strong></p>
        </div></section>




        <section class="pv3-section pv3-credentials" data-sales-step="7" aria-labelledby="pv3-credentials-title"><div class="pv3-wrap">
            <h2 id="pv3-credentials-title">Chi siamo, e perché veniamo di persona <em>invece di chiederti le foto.</em></h2>
            <p class="pv3-section-intro">Local Up lavora con attività locali che non hanno tempo di stare dietro ai social. Il nostro team va sul posto, gira i contenuti, li monta e li pubblica. Ecco alcuni profili che seguiamo.</p>
            <div class="pv3-clients">
                <article><strong>Pasticceria Claudio</strong><span>Pasticceria, Lucca</span><p>1.239.531 visualizzazioni in 90 giorni</p></article>
                <article><strong>Chiosco Focolento</strong><span>Ristorazione</span><p>143.972 visualizzazioni in 90 giorni</p></article>
                <article><strong>Piegaia Parrucchieri</strong><span>Salone</span><p>51.577 visualizzazioni in 90 giorni</p></article>
            </div>
            <p class="pv3-note">Dati Instagram Insight dei profili gestiti da Local Up, ultimi 90 giorni al momento della rilevazione.</p>
        </div></section>

        <section class="pv3-section pv3-benefits" data-sales-step="8" aria-labelledby="pv3-benefits-title"><div class="pv3-wrap">
            <h2 id="pv3-benefits-title">Cosa cambia quando i tuoi dolci si vedono <em>prima ancora di essere assaggiati.</em></h2>
            <div class="pv3-benefit-grid">
                <article><h3>Chi ti vede ha voglia di entrare</h3><p>Un video di lavorazione mostra quello che una foto in vetrina non dice: la cura, gli ingredienti, il prodotto appena fatto.</p></article>
                <article><h3>Spingi i dolci che ti fanno guadagnare</h3><p>Una torta che richiede ore deve far capire perché vale il suo prezzo. Mettiamo al centro quella, non il dolce qualsiasi.</p></article>
                <article><h3>Arrivi prima delle ricorrenze</h3><p>Compleanni, Natale, Pasqua, cerimonie. Chi ordina decide in anticipo: il tuo profilo deve esserci quando sceglie.</p></article>
                <article><h3>Ti riprendi le tue sere</h3><p>Niente più «domani devo pubblicare qualcosa». Il piano del mese è concordato e lo portiamo avanti noi.</p></article>
            </div>
        </div></section>

        <section class="pv3-section pv3-proof" id="prova" data-sales-step="9" aria-labelledby="pv3-proof-title"><div class="pv3-wrap pv3-proof-grid">
            <div>
                <p class="pv3-label">Non devi crederci sulla parola</p>
                <h2 id="pv3-proof-title">1.239.531 visualizzazioni in 90 giorni. <em>Apri il profilo e controlla.</em></h2>
                <p>Abbiamo curato i contenuti di <strong>Pasticceria Claudio</strong>, a Lucca. Negli ultimi 90 giorni il profilo Instagram ha avuto 1.239.531 visualizzazioni, ha raggiunto 717.045 persone e ha guadagnato 2.988 nuovi follower.</p>
                <p>Sono visualizzazioni, non scontrini. Ma nessuno entra a comprare un dolce che non ha mai visto.</p>
                <dl class="pv3-stats">
                    <div><dt>Visualizzazioni</dt><dd>1.239.531</dd></div>
                    <div><dt>Persone raggiunte</dt><dd>717.045</dd></div>
                    <div><dt>Nuovi follower</dt><dd>+2.988</dd></div>
                </dl>
                <a class="pv3-link" href="https://www.instagram.com/pasticceriaclaudiolucca/" target="_blank" rel="noopener noreferrer">Guarda @pasticceriaclaudiolucca <span aria-hidden="true">↗</span></a>
            </div>
            <figure class="pv3-insights"><img src="{{ asset('images/pasticcerie/insights.jpg') }}" alt="Insight Instagram di Pasticceria Claudio: 1.239.531 visualizzazioni e 2.988 follower netti negli ultimi 90 giorni" width="886" height="905" loading="lazy"><figcaption>@pasticceriaclaudiolucca, ultimi 90 giorni. Screenshot degli Insight di Instagram.</figcaption></figure>
        </div></section>

        <section class="pv3-section pv3-offer" aria-labelledby="pv3-offer-title"><div class="pv3-wrap">
            <p class="pv3-label" data-sales-step="10">Ecco la proposta</p>
            <h2 id="pv3-offer-title">Un mese intero di gestione social della tua pasticceria. <em>La nostra parte costa € 0.</em></h2>
            <p class="pv3-offer-lead">Non un PDF di consigli. Lavoro vero, sul tuo profilo, che puoi giudicare con i tuoi occhi.</p>
            <ul class="pv3-stack">
                <li><strong>Chiamata sulla tua pasticceria</strong><span>Guardiamo insieme il tuo profilo e scegliamo i prodotti da spingere.</span></li>
                <li><strong>Riprese in laboratorio</strong><span>Veniamo noi, con la nostra attrezzatura.</span></li>
                <li><strong>Montaggio di video e foto</strong><span>Contenuti pronti, pensati per far venire voglia.</span></li>
                <li><strong>Testi e pubblicazione</strong><span>Scriviamo e pubblichiamo secondo il piano concordato.</span></li>
            </ul>
            <div class="pv3-bonus" data-sales-step="11"><span>Bonus</span><div><h3>Prima di partire, guardiamo dove fai margine</h3><p>Parliamo dei prodotti che vuoi vendere, di quanto lavoro richiedono e di quando i clienti li comprano. Così il mese parte dal banco, non da un calendario di post.</p></div></div>
            <p class="pv3-value" data-sales-step="12">Fai il conto: riprese, montaggio, testi e pubblicazione sono <strong>quattro lavori che di solito pagheresti a quattro persone diverse</strong>. E per uno di questi qualcuno deve venire fisicamente nel tuo laboratorio.</p>
            <div class="pv3-price" data-sales-step="13"><div><span>Quanto paghi la nostra gestione nel primo mese?</span><strong>€ 0</strong></div><p>Il budget di eventuali annunci è separato: lo decidi tu e lo paghi direttamente a Meta, solo se scegli di fare campagne.</p></div>
        </div></section>

        <section class="pv3-section pv3-terms" aria-labelledby="pv3-terms-title"><div class="pv3-wrap pv3-terms-grid">
            <div data-sales-step="14"><h2 id="pv3-terms-title">Perché non possiamo dirlo a tutte le pasticcerie.</h2><p>Le riprese non si fanno da remoto: ogni pasticceria richiede una persona del nostro team in laboratorio, più il montaggio e la pubblicazione. Per questo ogni mese prendiamo un numero limitato di nuove attività e verifichiamo la disponibilità nella tua zona prima di partire.</p></div>
            <div class="pv3-guarantee" data-sales-step="15"><span>Nessun vincolo</span><h3>Dopo 30 giorni decidi tu, con il lavoro davanti agli occhi.</h3><p>Se ti convince, continuiamo. Se non ti convince, ci salutiamo e non ci devi niente. Nessun contratto da dodici mesi, nessuna sorpresa.</p></div>
        </div></section>

        <section class="pv3-section pv3-final" id="modulo" data-sales-step="16" aria-labelledby="pv3-final-title"><div class="pv3-wrap pv3-final-grid">
            <div>
                <h2 id="pv3-final-title">Ecco cosa succede quando premi il bottone.</h2>
                <ol class="pv3-next">
                    <li><span>01</span><p><strong>Compili due passaggi.</strong> Un minuto: nome della pasticceria, comune e un contatto.</p></li>
                    <li><span>02</span><p><strong>Ti chiamiamo entro 24 ore.</strong> Conosciamo i tuoi prodotti e verifichiamo la disponibilità nella tua zona.</p></li>
                    <li><span>03</span><p><strong>Fissiamo il giorno delle riprese.</strong> Partiamo solo dopo che hai approvato il piano del mese.</p></li>
                </ol>
            </div>
            <div class="pv3-form-shell">
                <div class="pv3-form-heading"><span>1 minuto, 2 passaggi</span><h3>Voglio il mese gratuito per la mia pasticceria</h3><p>Nessun impegno a continuare dopo i 30 giorni.</p></div>
                @include('social.partials.pastry-trial-form', ['placement' => 'bottom', 'formRoute' => 'pastry-v3.lead'])
            </div>
        </div></section>

        <section class="pv3-section pv3-faq" aria-labelledby="pv3-faq-title"><div class="pv3-wrap pv3-narrow">
            <h2 id="pv3-faq-title">Le domande che ci fanno <em>prima di dire sì.</em></h2>
            <div class="pv3-faq-list">
                <details><summary>Il mese è davvero gratuito?</summary><p>Sì. La nostra gestione descritta in questa pagina è gratuita per il primo mese. L'eventuale budget delle campagne è separato e lo paghi direttamente a Meta, solo se decidi di farle.</p></details>
                <details><summary>Devo fare io foto e video?</summary><p>No. Veniamo nella tua pasticceria, realizziamo i contenuti e ci occupiamo di montaggio, testi e pubblicazione.</p></details>
                <details><summary>Devo metterci la faccia?</summary><p>Solo se vuoi. I protagonisti sono i tuoi dolci, le mani che li preparano e il laboratorio. Ogni contenuto lo vedi prima che esca.</p></details>
                <details><summary>Devo decidere ora se continuare?</summary><p>No. Al termine del mese guardi il lavoro e decidi se proseguire. Non c'è nessun obbligo.</p></details>
                <details><summary>Lavorate anche nel mio comune?</summary><p>Dopo la richiesta verifichiamo la disponibilità per la tua zona e te lo diciamo nella prima telefonata.</p></details>
            </div>
        </div></section>

        <section class="pv3-ps" data-sales-step="17" aria-labelledby="pv3-ps-title"><div class="pv3-wrap pv3-narrow">
            <h2 id="pv3-ps-title">P.S.</h2>
            <p>Se sei arrivato fin qui senza leggere tutto: il primo mese di gestione social della tua pasticceria costa € 0. Veniamo noi in laboratorio, riprendiamo, montiamo e pubblichiamo. Ti basta un minuto per richiederlo.</p>
            <p><strong>Ogni giorno che aspetti, un altro vassoio finisce nel cestino mentre la pasticceria a tre chilometri si fa vedere al posto tuo.</strong></p>
            <a class="pv3-button" href="#modulo-hero">Richiedi il mese gratuito <span aria-hidden="true">↗</span></a>
        </div></section>
    </main>

    <footer class="trial-footer"><div class="trial-wrap trial-footer-inner"><div><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up"><p>Marketing digitale per attività locali in Toscana.</p></div><nav aria-label="Link legali"><a href="https://localupitalia.com/privacy-policy">Privacy</a><a href="https://localupitalia.com/cookie-policy">Cookie</a><a href="https://localupitalia.com/termini-e-condizioni">Termini</a>@unless(config('services.meta.track_without_consent'))<button type="button" class="cookie-preferences-open">Preferenze cookie</button>@endunless</nav><span>© {{ date('Y') }} Local Up · P.IVA 02727380467</span></div></footer>
    @include('partials.meta-tracking')
</body>
</html>

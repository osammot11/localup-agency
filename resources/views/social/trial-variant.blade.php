<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $landing['description'] }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $landing['title'] }}">
    <meta property="og:description" content="{{ $landing['description'] }}">
    <meta property="og:url" content="{{ route($landing['route'].'.landing') }}">
    <meta name="facebook-domain-verification" content="q5ql1uy3jhtg0uopl12bgc3a00z5ba">
    <title>{{ $landing['title'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/restaurants-trial.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trial-variants.css') }}">
    <script src="{{ asset('js/restaurants-trial.js') }}" defer></script>
</head>
<body class="trial-page trial-variant trial-variant-{{ $landing['key'] }}" data-submitted-form="{{ old('_form_placement') }}">
    <a class="trial-skip" href="#contenuto">Vai al contenuto</a>
    <header class="trial-header"><div class="trial-wrap trial-header-inner">
        <a href="{{ route($landing['route'].'.landing') }}" class="trial-logo" aria-label="Local Up, torna all'inizio della pagina"><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up"></a>
        <a class="trial-header-link" href="#modulo">{{ $landing['cta'] }} <span aria-hidden="true">↗</span></a>
    </div></header>

    <main id="contenuto">
        <section class="trial-hero" aria-labelledby="trial-title"><div class="trial-wrap trial-hero-grid">
            <div class="trial-hero-copy">
                <p class="trial-eyebrow"><span aria-hidden="true" class="trial-eyebrow-line"></span> {{ $landing['eyebrow'] }}</p>
                <h1 id="trial-title">{{ $landing['hero_title'] }} <em>{{ $landing['hero_emphasis'] }}</em></h1>
                <p class="trial-lead">{{ $landing['hero_lead'] }}</p>
                <p class="trial-promise">{{ $landing['promise'] }} <strong>{{ $landing['promise_emphasis'] }}</strong></p>
                <div id="modulo-hero" class="trial-hero-inline-form" role="region" aria-label="Modulo di richiesta">
                    <p class="trial-under-cta">{{ $landing['under_cta'] }}</p>
                    @include('social.partials.trial-variant-form', ['placement' => 'hero', 'landing' => $landing])
                </div>
            </div>
            <div class="trial-hero-art" aria-hidden="true">
                <div class="trial-art-orbit trial-art-orbit-one"></div><div class="trial-art-orbit trial-art-orbit-two"></div>
                <div class="trial-art-plate"><span>{{ $landing['art_primary'] }}</span><strong>{{ $landing['art_secondary'] }}</strong><small>@foreach(explode('<br>', $landing['art_small']) as $line){{ $line }}@if(! $loop->last)<br>@endif @endforeach</small></div>
                <div class="trial-art-note trial-art-note-top">{{ $landing['art_note_1'] }}</div><div class="trial-art-note trial-art-note-bottom">{{ $landing['art_note_2'] }}</div>
                <p class="trial-art-caption">LOCAL UP<br><strong>LA TUA ATTIVITÀ IN PRIMO PIANO.</strong></p>
            </div>
        </div><div class="trial-wrap trial-hero-foot">@foreach($landing['foot'] as $item)<span>{{ $item }}</span>@endforeach</div></section>

        <section class="trial-section trial-intro" aria-labelledby="trial-intro-title"><div class="trial-wrap trial-intro-grid">
            <div><p class="trial-kicker">{{ $landing['intro_kicker'] }}</p><h2 id="trial-intro-title">{{ $landing['intro_title'] }} <em>{{ $landing['intro_emphasis'] }}</em></h2></div>
            <div class="trial-prose">@foreach($landing['intro'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div>
        </div></section>

        <section class="trial-section trial-process" aria-labelledby="trial-process-title"><div class="trial-wrap">
            <p class="trial-kicker">{{ $landing['process_kicker'] }}</p><h2 id="trial-process-title">{{ $landing['process_title'] }} <em>{{ $landing['process_emphasis'] }}</em></h2>
            <div class="trial-cards">@foreach($landing['process'] as $step)<article class="trial-card"><span class="trial-card-number">{{ $step['label'] }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['text'] }}</p></article>@endforeach</div>
            <a class="trial-button trial-button-light" href="#modulo">{{ $landing['process_cta'] }} <span aria-hidden="true">↗</span></a>
        </div></section>

        <section class="trial-section trial-example" aria-labelledby="trial-example-title"><div class="trial-wrap trial-example-grid">
            <div class="trial-example-visual"><div class="trial-example-image"><img src="{{ asset($landing['example_image']) }}" alt="{{ $landing['example_name'] }}" loading="lazy"></div><span class="trial-example-stamp">Un esempio reale<br>del nostro lavoro</span></div>
            <div><p class="trial-kicker">{{ $landing['example_kicker'] }}</p><h2 id="trial-example-title">{{ $landing['example_title'] }} <em>{{ $landing['example_emphasis'] }}</em></h2><p class="trial-example-name">{{ $landing['example_name'] }}</p><p>{{ $landing['example_text'] }}</p><a class="trial-text-link" href="{{ $landing['example_url'] }}" target="_blank" rel="noopener noreferrer">{{ $landing['key'] === 'sites' ? 'Visita il sito' : 'Guarda il profilo Instagram' }} <span aria-hidden="true">↗</span></a><p class="trial-example-note">{{ $landing['example_note'] }}</p></div>
        </div></section>

        <section class="trial-section trial-cost" aria-labelledby="trial-offer-title"><div class="trial-wrap trial-cost-grid">
            <div><p class="trial-kicker">{{ $landing['offer_kicker'] }}</p><h2 id="trial-offer-title">{{ $landing['offer_title'] }}<br><em>{{ $landing['offer_emphasis'] }}</em></h2><div class="trial-zero">{{ $landing['offer_number'] }} <span>@foreach(explode('<br>', $landing['offer_label']) as $line){{ $line }}@if(! $loop->last)<br>@endif @endforeach</span></div></div>
            <div class="trial-cost-copy">@foreach($landing['offer_paragraphs'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach<div class="trial-cost-note"><strong>{{ $landing['offer_note_title'] }}</strong><p>{{ $landing['offer_note'] }}</p></div><a class="trial-button" href="#modulo">{{ $landing['cta'] }} <span aria-hidden="true">↗</span></a></div>
        </div></section>

        <section class="trial-section trial-start" aria-labelledby="trial-start-title"><div class="trial-wrap"><p class="trial-kicker">Semplice, dall'inizio</p><h2 id="trial-start-title">Come si comincia.</h2><ol class="trial-start-list">@foreach($landing['start'] as $step)<li><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><h3>{{ $step['title'] }}</h3><p>{{ $step['text'] }}</p></div></li>@endforeach</ol></div></section>

        <section class="trial-section trial-form-section" id="modulo" aria-labelledby="trial-form-title"><div class="trial-wrap trial-form-grid">
            <div class="trial-form-side"><p class="trial-kicker">La tua richiesta</p><h2 id="trial-form-title">{{ $landing['form_title'] }} <em>{{ $landing['form_emphasis'] }}</em></h2><p>{{ $landing['form_intro'] }}</p><div class="trial-form-side-note"><span aria-hidden="true">↗</span><p>Ti ricontattiamo entro 24 ore. Nessun impegno.</p></div></div>
            @include('social.partials.trial-variant-form', ['placement' => 'bottom', 'landing' => $landing])
        </div></section>

        <section class="trial-section trial-faq" aria-labelledby="trial-faq-title"><div class="trial-wrap trial-faq-grid"><div><p class="trial-kicker">Risposte chiare</p><h2 id="trial-faq-title">Domande <em>frequenti.</em></h2></div><div class="trial-faq-list">@foreach($landing['faq'] as $item)<details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>@endforeach</div></div></section>
        <section class="trial-final" aria-labelledby="trial-final-title"><div class="trial-wrap trial-final-inner"><p class="trial-kicker">{{ $landing['final_kicker'] }}</p><h2 id="trial-final-title">{{ $landing['final_title'] }} <em>{{ $landing['final_emphasis'] }}</em></h2><p>{{ $landing['final_text'] }}</p><a class="trial-button trial-button-light" href="#modulo">{{ $landing['cta'] }} <span aria-hidden="true">↗</span></a></div></section>
    </main>

    <footer class="trial-footer"><div class="trial-wrap trial-footer-inner"><div><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up"><p>Marketing digitale per attività locali in Toscana.</p></div><nav aria-label="Link legali"><a href="https://localupitalia.com/privacy-policy">Privacy</a><a href="https://localupitalia.com/cookie-policy">Cookie</a><a href="https://localupitalia.com/termini-e-condizioni">Termini</a>@unless(config('services.meta.track_without_consent'))<button type="button" class="cookie-preferences-open">Preferenze cookie</button>@endunless</nav><span>© {{ date('Y') }} Local Up · P.IVA 02727380467</span></div></footer>
    @include('partials.meta-tracking')
</body>
</html>

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
<body class="trial-page trial-variant trial-variant-{{ $landing['key'] }}">
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
                <a class="trial-button" href="#modulo">{{ $landing['cta'] }} <span aria-hidden="true">↗</span></a>
                <p class="trial-under-cta">{{ $landing['under_cta'] }}</p>
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
            <div class="trial-form-card">
                @if(request()->boolean('success'))
                    <div class="trial-success" role="status"><span class="trial-success-icon" aria-hidden="true">✓</span><p class="trial-kicker">Richiesta ricevuta</p><h3>{{ $landing['success_title'] }}</h3><p>{{ $landing['success_text'] }}</p><p>Nel frattempo, puoi guardare un esempio del nostro lavoro.</p><a class="trial-text-link" href="{{ $landing['example_url'] }}" target="_blank" rel="noopener noreferrer">Guarda il lavoro di Local Up <span aria-hidden="true">↗</span></a></div>
                @else
                    @if($errors->any())<div class="trial-form-error" role="alert">Controlla i campi evidenziati e riprova.</div>@endif
                    <form id="trial-lead-form" action="{{ route($landing['route'].'.lead') }}" method="post" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}">
                        @csrf
                        <div class="trial-honeypot" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="trial-progress" aria-label="Avanzamento modulo"><span class="trial-progress-label">Passaggio <strong data-current-step>1</strong> di 2</span><div class="trial-progress-track"><span data-progress-bar></span></div></div>
                        <fieldset class="trial-step" data-step="1"><legend>La tua attività</legend>
                            <div class="trial-field"><label for="attivita">{{ $landing['activity_label'] }} <span aria-hidden="true">*</span></label><input id="attivita" name="attivita" type="text" required maxlength="150" autocomplete="organization" value="{{ old('attivita') }}" @error('attivita') aria-invalid="true" aria-describedby="attivita-error" @enderror>@error('attivita')<p class="trial-field-error" id="attivita-error">{{ $message }}</p>@enderror</div>
                            <div class="trial-field"><label for="comune">Comune <span aria-hidden="true">*</span></label><input id="comune" name="comune" type="text" required maxlength="100" autocomplete="address-level2" value="{{ old('comune') }}" @error('comune') aria-invalid="true" aria-describedby="comune-error" @enderror>@error('comune')<p class="trial-field-error" id="comune-error">{{ $message }}</p>@enderror</div>
                            @if($landing['key'] === 'sites')
                                <div class="trial-field"><label for="settore">Settore <span aria-hidden="true">*</span></label><input id="settore" name="settore" type="text" required maxlength="150" value="{{ old('settore') }}" @error('settore') aria-invalid="true" aria-describedby="settore-error" @enderror>@error('settore')<p class="trial-field-error" id="settore-error">{{ $message }}</p>@enderror</div>
                                <div class="trial-field"><label for="sito_attuale">Sito attuale <span class="trial-optional">Facoltativo</span></label><input id="sito_attuale" name="sito_attuale" type="text" maxlength="255" autocomplete="url" placeholder="www.tuaattivita.it" value="{{ old('sito_attuale') }}" @error('sito_attuale') aria-invalid="true" aria-describedby="sito_attuale-error" @enderror>@error('sito_attuale')<p class="trial-field-error" id="sito_attuale-error">{{ $message }}</p>@enderror</div>
                            @else
                                <div class="trial-field"><label for="profilo_instagram">Profilo Instagram <span class="trial-optional">Facoltativo</span></label><input id="profilo_instagram" name="profilo_instagram" type="text" maxlength="255" autocomplete="url" placeholder="@nomeprofilo o link al profilo" value="{{ old('profilo_instagram') }}" @error('profilo_instagram') aria-invalid="true" aria-describedby="profilo_instagram-error" @enderror>@error('profilo_instagram')<p class="trial-field-error" id="profilo_instagram-error">{{ $message }}</p>@enderror</div>
                            @endif
                            <button class="trial-button trial-form-button" type="button" data-next-step>Continua <span aria-hidden="true">→</span></button>
                        </fieldset>
                        <fieldset class="trial-step" data-step="2"><legend>I tuoi contatti</legend>
                            <div class="trial-field"><label for="nome">Il tuo nome <span aria-hidden="true">*</span></label><input id="nome" name="nome" type="text" required maxlength="100" autocomplete="given-name" value="{{ old('nome') }}" @error('nome') aria-invalid="true" aria-describedby="nome-error" @enderror>@error('nome')<p class="trial-field-error" id="nome-error">{{ $message }}</p>@enderror</div>
                            @if($landing['key'] === 'sites')<div class="trial-field"><label for="cognome">Cognome <span aria-hidden="true">*</span></label><input id="cognome" name="cognome" type="text" required maxlength="100" autocomplete="family-name" value="{{ old('cognome') }}" @error('cognome') aria-invalid="true" aria-describedby="cognome-error" @enderror>@error('cognome')<p class="trial-field-error" id="cognome-error">{{ $message }}</p>@enderror</div>@endif
                            <div class="trial-field"><label for="telefono">Numero di telefono <span aria-hidden="true">*</span></label><input id="telefono" name="telefono" type="tel" required maxlength="30" autocomplete="tel" value="{{ old('telefono') }}" @error('telefono') aria-invalid="true" aria-describedby="telefono-error" @enderror>@error('telefono')<p class="trial-field-error" id="telefono-error">{{ $message }}</p>@enderror</div>
                            <div class="trial-field"><label for="email">Email <span aria-hidden="true">*</span></label><input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>@error('email')<p class="trial-field-error" id="email-error">{{ $message }}</p>@enderror</div>
                            <label class="trial-privacy"><input type="checkbox" name="consenso" value="1" required @checked(old('consenso')) @error('consenso') aria-invalid="true" aria-describedby="consenso-error" @enderror><span>Acconsento al trattamento dei miei dati per essere ricontattato in merito alla mia richiesta. <a href="https://localupitalia.com/privacy-policy" target="_blank" rel="noopener noreferrer">Privacy policy</a> <span aria-hidden="true">*</span></span></label>
                            @error('consenso')<p class="trial-field-error" id="consenso-error">{{ $message }}</p>@enderror
                            <div class="trial-form-actions"><button class="trial-back" type="button" data-prev-step>← Indietro</button><button class="trial-button trial-form-button" type="submit">Invia la richiesta <span aria-hidden="true">↗</span></button></div>
                        </fieldset>
                    </form>
                @endif
            </div>
        </div></section>

        <section class="trial-section trial-faq" aria-labelledby="trial-faq-title"><div class="trial-wrap trial-faq-grid"><div><p class="trial-kicker">Risposte chiare</p><h2 id="trial-faq-title">Domande <em>frequenti.</em></h2></div><div class="trial-faq-list">@foreach($landing['faq'] as $item)<details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>@endforeach</div></div></section>
        <section class="trial-final" aria-labelledby="trial-final-title"><div class="trial-wrap trial-final-inner"><p class="trial-kicker">{{ $landing['final_kicker'] }}</p><h2 id="trial-final-title">{{ $landing['final_title'] }} <em>{{ $landing['final_emphasis'] }}</em></h2><p>{{ $landing['final_text'] }}</p><a class="trial-button trial-button-light" href="#modulo">{{ $landing['cta'] }} <span aria-hidden="true">↗</span></a></div></section>
    </main>

    <footer class="trial-footer"><div class="trial-wrap trial-footer-inner"><div><img src="{{ asset('images/restaurants/logo.png') }}" alt="Local Up"><p>Marketing digitale per attività locali in Toscana.</p></div><nav aria-label="Link legali"><a href="https://localupitalia.com/privacy-policy">Privacy</a><a href="https://localupitalia.com/cookie-policy">Cookie</a><a href="https://localupitalia.com/termini-e-condizioni">Termini</a>@unless(config('services.meta.track_without_consent'))<button type="button" class="cookie-preferences-open">Preferenze cookie</button>@endunless</nav><span>© {{ date('Y') }} Local Up · P.IVA 02727380467</span></div></footer>
    @include('partials.meta-tracking')
</body>
</html>

<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $lead->nome }} {{ $lead->cognome }} | Contatti Local Up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="{{ asset('js/admin-contacts.js') }}" defer></script>
</head>
<body>
    <header class="admin-header">
        <div class="header-inner">
            <div class="admin-brand">LOCAL UP <span>ADMIN</span></div>
            <form action="{{ route('admin.logout') }}" method="post">
                @csrf
                <button class="logout" type="submit">Esci</button>
            </form>
        </div>
    </header>

    <main class="admin-main detail-main">
        <a class="back-link" href="{{ route('admin.leads.index', $context) }}">← Torna ai contatti</a>
        <div class="detail-heading">
            <div>
                <p class="eyebrow">Scheda contatto</p>
                <h1>{{ $lead->nome }} {{ $lead->cognome }}</h1>
                <p class="intro">Richiesta ricevuta il {{ $lead->created_at?->format('d/m/Y alle H:i') }}</p>
            </div>
            <nav class="contact-navigation" aria-label="Scorri i contatti">
                @if($previousId)
                    <a href="{{ route('admin.leads.show', ['lead' => $previousId] + $context) }}" aria-label="Contatto precedente, più recente" title="Contatto precedente">←</a>
                @else
                    <span aria-hidden="true">←</span>
                @endif
                @if($nextId)
                    <a href="{{ route('admin.leads.show', ['lead' => $nextId] + $context) }}" aria-label="Contatto successivo, meno recente" title="Contatto successivo">→</a>
                @else
                    <span aria-hidden="true">→</span>
                @endif
            </nav>
        </div>

        <section class="detail-card" aria-label="Dati del contatto">
            <div class="detail-field">
                <div><p class="detail-label">Email</p><p class="detail-value" id="contact-email">{{ $lead->email }}</p></div>
                <button class="copy-button" type="button" data-copy-target="contact-email" aria-label="Copia email">Copia</button>
            </div>
            <div class="detail-field">
                <div><p class="detail-label">Telefono</p><p class="detail-value" id="contact-phone">{{ $lead->telefono }}</p></div>
                <button class="copy-button" type="button" data-copy-target="contact-phone" aria-label="Copia telefono">Copia</button>
            </div>
            <div class="detail-field">
                <div><p class="detail-label">Sito web</p><p class="detail-value" id="contact-website">{{ $lead->sito_attuale ?: '—' }}</p></div>
                @if($lead->sito_attuale)
                    <button class="copy-button" type="button" data-copy-target="contact-website" aria-label="Copia sito web">Copia</button>
                @endif
            </div>
            <div class="detail-field"><div><p class="detail-label">Attività</p><p class="detail-value">{{ $lead->attivita }}</p></div></div>
            @if($lead->comune)<div class="detail-field"><div><p class="detail-label">Comune</p><p class="detail-value">{{ $lead->comune }}</p></div></div>@endif
            @if($lead->profilo_instagram)<div class="detail-field"><div><p class="detail-label">Profilo Instagram</p><p class="detail-value" id="contact-instagram">{{ $lead->profilo_instagram }}</p></div><button class="copy-button" type="button" data-copy-target="contact-instagram" aria-label="Copia profilo Instagram">Copia</button></div>@endif
            <div class="detail-field"><div><p class="detail-label">Settore</p><p class="detail-value">{{ $lead->settore }}</p></div></div>
            <div class="detail-field"><div><p class="detail-label">Landing</p><p class="detail-value">{{ match($lead->source) { 'social/ristoranti' => 'Ristoranti', 'social/ristoranti-v2' => 'Ristoranti · mese gratuito', 'social/beauty' => 'Beauty', 'social/beauty-v2' => 'Beauty · mese gratuito', 'social/antiquariato' => 'Antiquariato', 'social/antiquariato-v2' => 'Antiquariato · mese gratuito', 'social/pasticcerie' => 'Pasticcerie', 'social/pasticcerie-v2' => 'Pasticcerie · mese gratuito', 'siti' => 'Siti web', 'siti-v2' => 'Siti web · demo gratuita', default => $lead->source } }}</p></div></div>
            <div class="detail-field"><div><p class="detail-label">Consenso al ricontatto</p><p class="detail-value">{{ $lead->consented_at?->format('d/m/Y alle H:i') ?: '—' }}</p></div></div>
            <div class="detail-field"><div><p class="detail-label">Meta CAPI</p><p class="detail-value">{{ match($metaStatus) { 'sent' => 'Inviato', 'pending' => 'In attesa', 'failed' => 'Errore', 'cancelled' => 'Annullato', 'expired' => 'Scaduto', default => 'Non inviato' } }}</p></div></div>
        </section>
        <p class="copy-feedback" role="status" aria-live="polite"></p>
    </main>
</body>
</html>

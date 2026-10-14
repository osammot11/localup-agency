<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Contatti ricevuti | Local Up</title>
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

    <main class="admin-main">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Raccolta contatti</p>
                <h1>Richieste dalle landing</h1>
                <p class="intro">Consulta i contatti ricevuti da tutte le pagine Local Up.</p>
                <a class="admin-setting-link" href="{{ route('admin.google-sheets.show') }}">Impostazioni Google Sheets →</a>
            </div>
            <div class="count-card"><strong>{{ $leads->total() }}</strong><span>{{ $search !== '' || $source !== '' ? 'risultati' : 'contatti totali' }}</span></div>
        </div>

        <form class="filters" action="{{ route('admin.leads.index') }}" method="get" role="search">
            <div>
                <label for="q">Cerca un contatto</label>
                <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Nome, email, telefono o attività">
            </div>
            <div>
                <label for="source">Landing</label>
                <select id="source" name="source">
                    <option value="">Tutte le landing</option>
                    @foreach($sources as $value => $label)
                        <option value="{{ $value }}" @selected($source === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit">Filtra</button>
            @if($search !== '' || $source !== '')
                <a class="clear-filters" href="{{ route('admin.leads.index') }}">Azzera filtri</a>
            @endif
        </form>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Ricevuto</th><th>Contatto</th><th>Attività</th><th>Settore</th><th>Landing</th><th>Sito attuale</th><th>Consenso</th><th>Meta CAPI</th></tr></thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr class="contact-row" data-contact-url="{{ route('admin.leads.show', ['lead' => $lead->id] + $context) }}" tabindex="0" role="link" aria-label="Apri il contatto {{ $lead->nome }} {{ $lead->cognome }}">
                            <td class="date">{{ $lead->created_at?->format('d/m/Y H:i') }}</td>
                            <td><strong><a class="contact-name" href="{{ route('admin.leads.show', ['lead' => $lead->id] + $context) }}">{{ $lead->nome }} {{ $lead->cognome }}</a></strong><span>{{ $lead->email }}</span><span>{{ $lead->telefono }}</span></td>
                            <td>{{ $lead->attivita }}</td>
                            <td>{{ $lead->settore }}</td>
                            <td><span class="source-badge">{{ $sources[$lead->source] ?? $lead->source }}</span></td>
                            <td>{{ $lead->sito_attuale ?: '—' }}</td>
                            <td class="date">{{ $lead->consented_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ match($lead->meta_status) { 'sent' => 'Inviato', 'pending' => 'In attesa', 'failed' => 'Errore', 'cancelled' => 'Annullato', 'expired' => 'Scaduto', default => 'Senza consenso marketing' } }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="8">Nessun contatto trovato.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <nav class="pagination" aria-label="Pagine dei contatti">
                @if($leads->onFirstPage())<span>← Precedenti</span>@else<a href="{{ $leads->previousPageUrl() }}">← Precedenti</a>@endif
                <span>Pagina {{ $leads->currentPage() }} di {{ $leads->lastPage() }}</span>
                @if($leads->hasMorePages())<a href="{{ $leads->nextPageUrl() }}">Successivi →</a>@else<span>Successivi →</span>@endif
            </nav>
        @endif
    </main>
</body>
</html>

<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Google Sheets | Local Up Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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

    <main class="admin-main settings-main">
        <a class="back-link" href="{{ route('admin.leads.index') }}">← Torna ai contatti</a>
        <p class="eyebrow">Integrazioni</p>
        <h1>Google Sheets</h1>
        <p class="intro">I contatti restano salvati nell’admin. Qui puoi inviarne una copia a un foglio Google e cambiare destinazione in autonomia.</p>

        @if(session('status'))<div class="settings-notice success" role="status">{{ session('status') }}</div>@endif
        @if(session('error'))<div class="settings-notice error" role="alert">{{ session('error') }}</div>@endif
        @if($errors->any())
            <div class="settings-notice error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="settings-status" aria-label="Stato sincronizzazione">
            <div><strong>{{ $total }}</strong><span>contatti nell’admin</span></div>
            <div><strong>{{ $pending }}</strong><span>da inviare a questo foglio</span></div>
            <div><strong>{{ $settings?->enabled ? 'Attiva' : 'Disattivata' }}</strong><span>sincronizzazione</span></div>
        </section>

        @if($settings?->last_error)<div class="settings-notice error" role="alert">Ultimo errore: {{ $settings->last_error }}</div>@endif
        @if($settings?->last_synced_at)<p class="settings-meta">Ultima sincronizzazione riuscita: {{ \Illuminate\Support\Carbon::parse($settings->last_synced_at)->timezone('Europe/Rome')->format('d/m/Y H:i') }}</p>@endif

        <div class="settings-layout">
            <form class="settings-card" action="{{ route('admin.google-sheets.update') }}" method="post">
                @csrf
                <h2>Collegamento</h2>
                <label for="spreadsheet">Link del Google Sheet o ID</label>
                <input id="spreadsheet" name="spreadsheet" type="text" value="{{ old('spreadsheet', $settings?->spreadsheet_id ? 'https://docs.google.com/spreadsheets/d/'.$settings->spreadsheet_id.'/edit' : '') }}" placeholder="https://docs.google.com/spreadsheets/d/…/edit" autocomplete="off">
                <p class="settings-help">Puoi sostituire il link quando vuoi. I contatti già presenti nell’admin verranno inviati anche al nuovo foglio.</p>

                <label for="sheet_name">Nome della scheda</label>
                <input id="sheet_name" name="sheet_name" type="text" value="{{ old('sheet_name', $settings?->sheet_name ?? 'Contatti Local Up') }}" required maxlength="100">
                <p class="settings-help">Se non esiste, viene creata. Usa una scheda vuota o già creata da Local Up.</p>

                <label for="credentials_json">Credenziali JSON del service account</label>
                <textarea id="credentials_json" name="credentials_json" rows="6" autocomplete="off" spellcheck="false" placeholder="Incolla qui il contenuto del file JSON Google"></textarea>
                <p class="settings-help">Il campo resta vuoto dopo il salvataggio per proteggere la chiave. Lascialo vuoto per mantenere le credenziali attuali; incolla un nuovo JSON per sostituirle.</p>
                @if($settings?->service_account_email)
                    <p class="service-email">Account collegato: <strong>{{ $settings->service_account_email }}</strong></p>
                @endif

                <input type="hidden" name="enabled" value="0">
                <label class="settings-check"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings?->enabled))> Attiva la sincronizzazione automatica</label>
                <button class="settings-primary" type="submit">Salva impostazioni</button>
            </form>

            <aside class="settings-card settings-guide">
                <h2>Come collegarlo</h2>
                <ol>
                    <li>Nel progetto Google Cloud abilita la <strong>Google Sheets API</strong> e crea un <strong>service account</strong> con una chiave JSON.</li>
                    <li>Incolla qui il JSON e salva: comparirà l’indirizzo email del service account.</li>
                    <li>Apri il Google Sheet, premi <strong>Condividi</strong> e assegna a quell’indirizzo il ruolo <strong>Editor</strong>.</li>
                    <li>Inserisci il link del foglio, attiva il collegamento e premi <strong>Sincronizza ora</strong>.</li>
                </ol>
                <p class="settings-help"><a href="https://developers.google.com/identity/protocols/oauth2/service-account" target="_blank" rel="noopener noreferrer">Guida ufficiale ai service account Google</a></p>
                <p class="settings-help">Dopo la configurazione, i nuovi contatti vengono inviati ogni 5 minuti se lo scheduler Laravel è attivo sul server. Se Google non risponde, restano nell’admin e il tentativo successivo li recupera.</p>
                <form action="{{ route('admin.google-sheets.sync') }}" method="post">
                    @csrf
                    <button class="settings-secondary" type="submit" @disabled(! $settings?->enabled)>Sincronizza ora</button>
                </form>
                <p class="settings-help">Il comando manuale elabora fino a 10 contatti; quello automatico fino a 50 per esecuzione.</p>
            </aside>
        </div>
    </main>
</body>
</html>

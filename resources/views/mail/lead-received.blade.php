<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Nuovo contatto Local Up</title>
</head>
<body>
    <h1>Nuovo contatto ricevuto</h1>
    <p><strong>Nome:</strong> {{ $lead->nome }}</p>
    <p><strong>Cognome:</strong> {{ $lead->cognome }}</p>
    <p><strong>Email:</strong> {{ $lead->email }}</p>
    <p><strong>Telefono:</strong> {{ $lead->telefono }}</p>
    <p><strong>Attività:</strong> {{ $lead->attivita }}</p>
    <p><strong>Settore:</strong> {{ $lead->settore }}</p>
    <p><strong>Sito attuale:</strong> {{ $lead->sito_attuale ?: '—' }}</p>
    <p><strong>Landing:</strong> {{ $lead->source }}</p>
    <p><strong>Ricevuto il:</strong> {{ $lead->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') }} (ora italiana)</p>
    <p><a href="{{ route('admin.leads.show', $lead) }}">Apri il contatto nell’area admin</a></p>
</body>
</html>

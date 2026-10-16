Nuovo contatto ricevuto

Nome: {!! $lead->nome !!}
Cognome: {!! $lead->cognome !!}
Email: {!! $lead->email !!}
Telefono: {!! $lead->telefono !!}
Attività: {!! $lead->attivita !!}
Settore: {!! $lead->settore !!}
Sito attuale: {!! $lead->sito_attuale ?: '—' !!}
Landing: {!! $lead->source !!}
Ricevuto il: {!! $lead->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') !!} (ora italiana)

Apri il contatto nell’area admin: {!! route('admin.leads.show', $lead) !!}

Nuovo contatto ricevuto

Nome: {!! $lead->nome !!}
@if($lead->cognome)Cognome: {!! $lead->cognome !!}
@endif
Email: {!! $lead->email !!}
Telefono: {!! $lead->telefono !!}
Attività: {!! $lead->attivita !!}
@if($lead->comune)Comune: {!! $lead->comune !!}
@endif
@if($lead->profilo_instagram)Profilo Instagram: {!! $lead->profilo_instagram !!}
@endif
Settore: {!! $lead->settore !!}
Sito attuale: {!! $lead->sito_attuale ?: '—' !!}
Landing: {!! $lead->source !!}
Ricevuto il: {!! $lead->created_at->timezone('Europe/Rome')->format('d/m/Y H:i') !!} (ora italiana)

Apri il contatto nell’area admin: {!! route('admin.leads.show', $lead) !!}

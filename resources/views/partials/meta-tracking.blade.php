@php
    $marketingState = app(\App\Services\MarketingConsent::class)->state(request());
    $metaPixelId = config('services.meta.pixel_id');
@endphp

<aside class="cookie-panel" id="cookie-panel" aria-labelledby="cookie-panel-title" @if($marketingState !== null) hidden @endif>
    <h2 id="cookie-panel-title">Cookie e tracciamento</h2>
    <p>Con il tuo consenso usiamo Meta Pixel e Conversions API per misurare le visite e le richieste inviate. Puoi rifiutare e continuare a usare il sito, oppure cambiare scelta in qualsiasi momento.</p>
    <div class="cookie-panel-links"><a href="https://localupitalia.com/privacy-policy" target="_blank" rel="noopener noreferrer">Privacy policy</a><a href="https://localupitalia.com/cookie-policy" target="_blank" rel="noopener noreferrer">Cookie policy</a></div>
    <div class="cookie-panel-actions">
        <form action="{{ route('tracking.consent') }}" method="post">@csrf<input type="hidden" name="choice" value="rejected"><input type="hidden" name="return_to" value="{{ request()->getPathInfo() }}"><button type="submit">Rifiuta</button></form>
        <form action="{{ route('tracking.consent') }}" method="post">@csrf<input type="hidden" name="choice" value="granted"><input type="hidden" name="return_to" value="{{ request()->getPathInfo() }}"><button type="submit">Accetta</button></form>
    </div>
</aside>
<script src="{{ asset('js/cookie-preferences.js') }}" defer></script>

@if($marketingState === 'granted' && is_string($metaPixelId) && preg_match('/^\d+$/', $metaPixelId))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', @json($metaPixelId));
        fbq('track', 'PageView');
        @if(session('meta_lead_event_id'))
            fbq('track', 'Lead', {}, {eventID: @json(session('meta_lead_event_id'))});
        @endif
        document.querySelectorAll('a[href*="wa.me/"]').forEach(function(link) {
            link.addEventListener('click', function() { fbq('track', 'Contact', {content_name: 'WhatsApp'}); });
        });
    </script>
@endif

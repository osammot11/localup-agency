<?php

namespace Tests\Feature;

use App\Mail\LeadReceived;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TrialLandingVariantsTest extends TestCase
{
    use RefreshDatabase;

    public static function variants(): array
    {
        return [
            'beauty' => ['beauty-trial', 'social/beauty-v2', 'Il tuo lavoro si vede dal vivo.', 'Beauty'],
            'antiquariato' => ['antiques-trial', 'social/antiquariato-v2', 'Ogni pezzo ha una storia.', 'Antiquariato'],
            'pasticcerie' => ['pastry-trial', 'social/pasticcerie-v2', 'I tuoi dolci fanno venire voglia.', 'Pasticceria'],
            'siti' => ['sites-trial', 'siti-v2', 'Il tuo sito dovrebbe lavorare per te.', 'Ristorazione'],
        ];
    }

    #[DataProvider('variants')]
    public function test_each_second_landing_renders_its_offer_and_two_step_form(string $route, string $source, string $headline, string $sector): void
    {
        $this->get(route($route.'.landing'))
            ->assertOk()
            ->assertSee($headline)
            ->assertSee('data-step="1"', false)
            ->assertSee('data-step="2"', false)
            ->assertSee('trial-variants.css')
            ->assertSee('restaurants-trial.js')
            ->assertSee('connect.facebook.net')
            ->assertDontSee('tailwind');
    }

    public function test_pasticcerie_uses_its_own_page_with_two_independent_forms(): void
    {
        $this->get(route('pastry-trial.landing'))
            ->assertOk()
            ->assertViewIs('social.pastry-trial')
            ->assertSee('id="hero-trial-lead-form"', false)
            ->assertSee('id="trial-lead-form"', false)
            ->assertSee('name="_form_placement" value="hero"', false)
            ->assertSee('name="_form_placement" value="bottom"', false);
    }

    #[DataProvider('variants')]
    public function test_each_second_landing_saves_a_lead_and_queues_email(string $route, string $source, string $headline, string $sector): void
    {
        config()->set('mail.lead_recipients', 'team@example.com');
        config()->set('services.meta.access_token', '');
        Mail::fake();
        $lead = $this->lead($route === 'sites-trial');

        $this->post(route($route.'.lead'), $lead)
            ->assertRedirect(route($route.'.landing', ['success' => 1]).'#modulo');

        $this->assertDatabaseHas('leads', [
            'attivita' => 'La Bottega',
            'comune' => 'Lucca',
            'email' => 'mario@example.com',
            'source' => $source,
            'settore' => $sector,
        ]);
        Mail::assertQueued(LeadReceived::class, 1);

        $this->get(route($route.'.landing', ['success' => 1]))
            ->assertOk()
            ->assertSee('Richiesta ricevuta');
    }

    #[DataProvider('variants')]
    public function test_each_second_landing_rejects_incomplete_or_spam_requests(string $route, string $source, string $headline, string $sector): void
    {
        $this->post(route($route.'.lead'), [
            'attivita' => 'La Bottega',
            'nome' => 'Mario',
            'telefono' => '+39 320 000 0000',
            'email' => 'mario@example.com',
        ])->assertSessionHasErrors(['comune', 'consenso']);

        $this->post(route($route.'.lead'), $this->lead($route === 'sites-trial') + ['website' => 'spam'])
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_website_form_keeps_its_fields_after_a_contact_error(): void
    {
        $this->followingRedirects()->from(route('sites-trial.landing'))
            ->post(route('sites-trial.lead'), array_replace($this->lead(true), ['email' => 'non-valida']))
            ->assertOk()
            ->assertSee('data-has-errors="true"', false)
            ->assertSee('value="www.labottega.it"', false)
            ->assertSee('aria-invalid="true" aria-describedby="email-error"', false);
    }

    public function test_second_website_landing_shows_the_final_price_without_iva(): void
    {
        $this->get(route('sites-trial.landing'))
            ->assertOk()
            ->assertSee('Il sito completo costa 850 €.')
            ->assertSee('una volta sola')
            ->assertDontSee('850 € + IVA')
            ->assertDontSee('€ + IVA');
    }

    public function test_new_sources_can_be_filtered_in_admin_and_detail_remains_protected(): void
    {
        config()->set('services.meta.access_token', '');
        $this->post(route('sites-trial.lead'), $this->lead(true))->assertRedirect();
        $leadId = DB::table('leads')->value('id');

        $this->get(route('admin.leads.show', $leadId))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get(route('admin.leads.index', ['source' => 'siti-v2']))
            ->assertOk()
            ->assertSee('La Bottega');
        $this->get(route('admin.leads.show', $leadId))
            ->assertOk()
            ->assertSee('Siti web · demo gratuita')
            ->assertSee('www.labottega.it');
    }

    public function test_new_landing_meta_lead_uses_its_own_source(): void
    {
        config()->set('services.meta.access_token', 'test-token');
        Http::preventStrayRequests();
        Http::fake(['https://graph.facebook.com/v26.0/1612527090508674/events' => Http::response(['events_received' => 1])]);

        $this->post(route('beauty-trial.lead'), $this->lead(false))
            ->assertRedirect()
            ->assertSessionHas('meta_lead_event_id');

        $this->assertDatabaseHas('meta_events', ['status' => 'sent']);
        Http::assertSent(fn ($request): bool => $request['data'][0]['custom_data']['content_name'] === 'social/beauty-v2');
    }

    public function test_cookie_choice_returns_to_each_new_landing(): void
    {
        foreach (['/social/beauty-v2', '/social/antiquariato-v2', '/social/pasticcerie-v2', '/siti-v2'] as $path) {
            $this->post(route('tracking.consent'), ['choice' => 'granted', 'return_to' => $path])
                ->assertRedirect($path);
        }
    }

    private function lead(bool $website): array
    {
        $data = [
            'attivita' => 'La Bottega',
            'comune' => 'Lucca',
            'nome' => 'Mario',
            'telefono' => '+39 320 000 0000',
            'email' => 'mario@example.com',
            'consenso' => '1',
        ];

        return $website
            ? $data + ['cognome' => 'Rossi', 'settore' => 'Ristorazione', 'sito_attuale' => 'www.labottega.it']
            : $data + ['profilo_instagram' => '@labottega'];
    }
}

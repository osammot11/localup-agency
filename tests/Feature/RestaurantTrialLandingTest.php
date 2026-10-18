<?php

namespace Tests\Feature;

use App\Mail\LeadReceived;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RestaurantTrialLandingTest extends TestCase
{
    use RefreshDatabase;

    private function lead(): array
    {
        return [
            'attivita' => 'Osteria del Ponte',
            'comune' => 'Lucca',
            'profilo_instagram' => '@osteriadelponte',
            'nome' => 'Mario Rossi',
            'telefono' => '+39 320 000 0000',
            'email' => 'mario@example.com',
            'consenso' => '1',
        ];
    }

    public function test_second_restaurant_landing_shows_requested_sections_and_two_step_form(): void
    {
        $this->get(route('restaurants-trial.landing'))
            ->assertOk()
            ->assertSee('Il tuo ristorante fa venire fame')
            ->assertSee('Cosa facciamo durante')
            ->assertSee('Chiosco Focolento')
            ->assertSee('Il primo mese:')
            ->assertSee('Come si comincia')
            ->assertSee('Domande')
            ->assertSee('data-step="1"', false)
            ->assertSee('data-step="2"', false)
            ->assertSee('restaurants-trial.js')
            ->assertSee('connect.facebook.net')
            ->assertDontSee('tailwind');
    }

    public function test_trial_request_saves_all_fields_and_queues_email_and_meta_lead(): void
    {
        config()->set('mail.lead_recipients', 'team@example.com');
        config()->set('services.meta.access_token', 'test-token');
        Mail::fake();
        Http::preventStrayRequests();
        Http::fake(['https://graph.facebook.com/v26.0/1612527090508674/events' => Http::response(['events_received' => 1])]);

        $this->post(route('restaurants-trial.lead'), $this->lead())
            ->assertRedirect(route('restaurants-trial.landing', ['success' => 1]).'#modulo')
            ->assertSessionHas('meta_lead_event_id');

        $this->assertDatabaseHas('leads', [
            'nome' => 'Mario Rossi',
            'cognome' => '',
            'attivita' => 'Osteria del Ponte',
            'comune' => 'Lucca',
            'profilo_instagram' => '@osteriadelponte',
            'settore' => 'Ristorazione',
            'source' => 'social/ristoranti-v2',
        ]);
        $this->assertDatabaseHas('meta_events', ['lead_id' => DB::table('leads')->value('id'), 'status' => 'sent']);
        Mail::assertQueued(LeadReceived::class, 1);
        Http::assertSent(fn ($request) => $request['data'][0]['event_name'] === 'Lead'
            && $request['data'][0]['custom_data']['content_name'] === 'social/ristoranti-v2');
        $this->get(route('restaurants-trial.landing', ['success' => 1]))
            ->assertOk()
            ->assertSee('Richiesta ricevuta')
            ->assertSee('eventID:', false);
    }

    public function test_trial_request_requires_both_steps_and_rejects_honeypot(): void
    {
        $this->post(route('restaurants-trial.lead'), [
            'attivita' => 'Osteria del Ponte',
            'nome' => 'Mario Rossi',
            'telefono' => '+39 320 000 0000',
            'email' => 'mario@example.com',
        ])->assertSessionHasErrors(['comune', 'consenso']);

        $this->post(route('restaurants-trial.lead'), $this->lead() + ['website' => 'spam'])
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_hero_trial_form_returns_to_itself_and_retains_validation_placement(): void
    {
        $this->post(route('restaurants-trial.lead'), $this->lead() + ['_form_placement' => 'hero'])
            ->assertRedirect(route('restaurants-trial.landing', ['success' => 1]).'#modulo-hero');

        $this->assertDatabaseCount('leads', 1);

        $this->followingRedirects()->from(route('restaurants-trial.landing'))
            ->post(route('restaurants-trial.lead'), array_replace($this->lead(), [
                'email' => 'non-valida',
                '_form_placement' => 'hero',
            ]))
            ->assertOk()
            ->assertSee('data-submitted-form="hero"', false)
            ->assertSee('data-has-errors="true"', false);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_invalid_contact_step_keeps_restaurant_fields_for_correction(): void
    {
        $this->followingRedirects()->from(route('restaurants-trial.landing'))
            ->post(route('restaurants-trial.lead'), array_replace($this->lead(), ['email' => 'non-valida']))
            ->assertOk()
            ->assertSee('data-has-errors="true"', false)
            ->assertSee('value="Osteria del Ponte"', false)
            ->assertSee('value="Lucca"', false)
            ->assertSee('aria-invalid="true" aria-describedby="email-error"', false);
    }

    public function test_trial_details_appear_in_protected_admin_and_email(): void
    {
        config()->set('mail.lead_recipients', 'team@example.com');
        config()->set('services.meta.access_token', '');
        Mail::fake();
        $this->post(route('restaurants-trial.lead'), $this->lead())->assertRedirect();
        $leadId = DB::table('leads')->value('id');

        $this->get(route('admin.leads.show', $leadId))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get(route('admin.leads.show', $leadId))
            ->assertOk()
            ->assertSee('Lucca')
            ->assertSee('@osteriadelponte')
            ->assertSee('Ristoranti · mese gratuito');

        Mail::assertQueued(LeadReceived::class, function (LeadReceived $mail): bool {
            return str_contains($mail->render(), 'Lucca') && str_contains($mail->render(), '@osteriadelponte');
        });
    }
}

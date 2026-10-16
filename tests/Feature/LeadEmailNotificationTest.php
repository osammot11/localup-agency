<?php

namespace Tests\Feature;

use App\Mail\LeadReceived;
use App\Models\Lead;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class LeadEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function leadData(): array
    {
        return [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Osteria del Podere',
            'settore' => 'Ristorazione',
            'consenso' => '1',
        ];
    }

    public function test_every_landing_queues_one_email_per_distinct_recipient(): void
    {
        config()->set('mail.lead_recipients', 'primo@example.com, secondo@example.com, PRIMO@example.com, ,');
        Mail::fake();

        foreach (['restaurants', 'sites', 'beauty', 'antiques', 'pastry'] as $landing) {
            $this->post(route($landing.'.lead'), $this->leadData())
                ->assertRedirect(route($landing.'.landing', ['success' => 1]).'#modulo');
        }

        $this->assertDatabaseCount('leads', 5);
        Mail::assertQueuedCount(10);
        Mail::assertQueued(LeadReceived::class, fn (LeadReceived $mail): bool => $mail->hasTo('primo@example.com')
            && $mail->connection === 'database' && $mail->queue === 'lead-mail');
        Mail::assertQueued(LeadReceived::class, fn (LeadReceived $mail): bool => $mail->hasTo('secondo@example.com'));
        Mail::assertNothingSent();
    }

    public function test_email_contains_every_contact_field_and_protected_detail_link(): void
    {
        $this->freezeTime();
        $lead = Lead::create([
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => '<script>alert(1)</script>',
            'settore' => 'Ristorazione',
            'sito_attuale' => 'www.osteriadelpodere.it',
            'source' => 'siti',
            'consented_at' => now(),
        ]);
        $mail = new LeadReceived($lead);

        foreach (['Mario', 'Rossi', 'mario@example.com', '+39 320 000 0000', 'Ristorazione',
            'www.osteriadelpodere.it', 'siti', route('admin.leads.show', $lead),
            $lead->created_at->timezone('Europe/Rome')->format('d/m/Y H:i')] as $value) {
            $mail->assertSeeInHtml($value);
            $mail->assertSeeInText($value);
        }

        $mail->assertSeeInHtml('&lt;script&gt;alert(1)&lt;/script&gt;', escape: false)
            ->assertDontSeeInHtml('<script>alert(1)</script>', escape: false)
            ->assertSeeInText('<script>alert(1)</script>');
        $this->get(route('admin.leads.show', $lead))->assertRedirect(route('login'));
    }

    public function test_scheduled_queue_can_send_only_lead_email_jobs(): void
    {
        config()->set('mail.lead_recipients', 'avvisi@example.com');
        $this->post(route('restaurants.lead'), $this->leadData())->assertRedirect();
        $this->assertDatabaseHas('jobs', ['queue' => 'lead-mail']);

        $this->artisan('queue:work database --queue=lead-mail --stop-when-empty --max-jobs=25 --timeout=20 --tries=5 --backoff=60')
            ->assertSuccessful();

        $this->assertDatabaseCount('jobs', 0);
        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $this->assertSame('avvisi@example.com', $messages[0]->getOriginalMessage()->getTo()[0]->getAddress());
    }

    public function test_mail_delivery_failure_keeps_the_lead_and_records_a_failed_job(): void
    {
        config()->set('mail.lead_recipients', 'avvisi@example.com');
        $this->post(route('restaurants.lead'), $this->leadData())->assertRedirect();
        $this->mock(MailFactory::class)
            ->shouldReceive('mailer')
            ->andThrow(new RuntimeException('SMTP non disponibile'));

        $this->artisan('queue:work database --queue=lead-mail --once --timeout=20 --tries=1')
            ->assertSuccessful();

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('failed_jobs', 1);
    }

    public function test_no_recipient_keeps_lead_without_queueing_email(): void
    {
        config()->set('mail.lead_recipients', '');

        $this->post(route('restaurants.lead'), $this->leadData())->assertRedirect();

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('jobs', 0);
    }
}

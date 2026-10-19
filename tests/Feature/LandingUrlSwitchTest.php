<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LandingUrlSwitchTest extends TestCase
{
    public static function landingUrls(): array
    {
        return [
            'ristoranti' => ['/social/ristoranti', 'restaurants-trial', 'Riempiamo il tuo ristorante.', 'restaurants', 'Sala piena'],
            'beauty' => ['/social/beauty', 'beauty-trial', 'Il tuo lavoro si vede dal vivo.', 'beauty', 'Agenda piena tutta la settimana'],
            'antiquariato' => ['/social/antiquariato', 'antiques-trial', 'Ogni pezzo ha una storia.', 'antiques', 'I tuoi pezzi davanti ai'],
            'pasticcerie' => ['/social/pasticcerie', 'pastry-trial', 'I tuoi dolci fanno venire voglia.', 'pastry', 'Più ordini di'],
            'siti' => ['/siti', 'sites-trial', 'Il tuo sito dovrebbe lavorare per te.', 'sites', 'senza pensieri'],
        ];
    }

    #[DataProvider('landingUrls')]
    public function test_campaign_url_serves_new_landing_and_v2_url_serves_previous_landing(
        string $campaignPath,
        string $newRoute,
        string $newHeadline,
        string $previousRoute,
        string $previousHeadline,
    ): void {
        $previousPath = $campaignPath.'-v2';

        $this->assertSame(url($campaignPath), route($newRoute.'.landing'));
        $this->assertSame(url($previousPath), route($previousRoute.'.landing'));

        $this->get($campaignPath)
            ->assertOk()
            ->assertSee($newHeadline)
            ->assertSee('action="'.route($newRoute.'.lead').'"', false);

        $this->get($previousPath)
            ->assertOk()
            ->assertSee($previousHeadline)
            ->assertSee('action="'.route($previousRoute.'.lead').'"', false);
    }
}

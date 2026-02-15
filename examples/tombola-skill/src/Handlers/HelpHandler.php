<?php

declare(strict_types=1);

namespace TombolaNapoletana\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Response\Response;
use TombolaNapoletana\Services\TombolaManager;
use TombolaNapoletana\Services\SpeechBuilder;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Handler for help requests
 * @utterances aiuto, aiutami, cosa posso fare, comandi
 * @utterances quali sono i comandi, istruzioni, come si gioca
 */
class HelpHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        $appId = TombolaConfig::getAlexaAppId();
        return empty($appId) || $request->session->application->applicationId === $appId;
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest &&
            $request->request->intent->name === 'AMAZON.HelpIntent';
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        $extractedCount = $tombola->getExtractedCount();
        
        // Build contextual help based on game state
        if ($extractedCount === 0) {
            $speech = $this->buildBeginnerHelp();
        } elseif ($tombola->isComplete()) {
            $speech = $this->buildCompleteHelp();
        } else {
            $speech = $this->buildGameHelp($extractedCount);
        }
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => $tombola->getLastNumber(),
                'last_smorfia' => $tombola->getLastSmorfia(),
                'extracted_count' => $tombola->getExtractedCount(),
                'user_id' => $userId
            ])
            ->reprompt("Cosa vuoi fare?")
            ->build();
    }
    
    /**
     * Build help for new players
     */
    private function buildBeginnerHelp(): string
    {
        return SpeechBuilder::create()
            ->sentence("Benvenuto nell'aiuto della Tombola Napoletana!")
            ->break("500ms")
            ->sentence("Per iniziare a giocare, dici 'estrai un numero'.")
            ->break("300ms")
            ->sentence("Estrarrò un numero da 1 a 90 con la sua smorfia napoletana.")
            ->break("500ms")
            ->sentence("Altri comandi utili:")
            ->break("300ms")
            ->sentence("Per ripetere: 'ripeti il numero'")
            ->break("300ms")
            ->sentence("Per verificare: 'è uscito il 25'")
            ->break("300ms")
            ->sentence("Per vincite: 'ambo', 'terna', 'quaterna', 'cinquina', 'tombola'")
            ->break("300ms")
            ->sentence("Per modalità: 'modalità automatica', 'modalità lenta'")
            ->break("500ms")
            ->sentence("Dimmi 'estrai un numero' quando sei pronto!")
            ->build();
    }
    
    /**
     * Build help for active game
     */
    private function buildGameHelp(int $extractedCount): string
    {
        return SpeechBuilder::create()
            ->sentence("Ecco i comandi disponibili per la Tombola Napoletana:")
            ->break("500ms")
            ->sentence("Per giocare: 'estrai un numero', 'prossimo numero', 'continua'")
            ->break("300ms")
            ->sentence("Per ripetere: 'ripeti', 'ripeti il numero'")
            ->break("300ms")
            ->sentence("Per verificare: 'è uscito il numero', seguito dal numero")
            ->break("300ms")
            ->sentence("Per vincite: 'ambo', 'terna', 'quaterna', 'cinquina', 'tombola'")
            ->break("300ms")
            ->sentence("Per modalità: 'modalità automatica', 'modalità lenta', 'modalità normale'")
            ->break("300ms")
            ->sentence("Per controllo: 'stato', 'pausa', 'nuova partita', 'termina partita'")
            ->break("500ms")
            ->sentence("Hai già estratto {$extractedCount} numeri. Cosa vuoi fare?")
            ->build();
    }
    
    /**
     * Build help for completed game
     */
    private function buildCompleteHelp(): string
    {
        return SpeechBuilder::create()
            ->sentence("La partita è completata! Hai estratto tutti i 90 numeri.")
            ->break("500ms")
            ->sentence("Puoi iniziare una nuova partita dicendo:")
            ->break("300ms")
            ->sentence("'nuova partita', 'ricomincia', 'inizia nuova partita'")
            ->break("500ms")
            ->sentence("Oppure puoi verificare le tue vincite dicendo:")
            ->break("300ms")
            ->sentence("'ambo', 'terna', 'quaterna', 'cinquina', 'tombola'")
            ->break("500ms")
            ->sentence("Cosa vuoi fare?")
            ->build();
    }
}

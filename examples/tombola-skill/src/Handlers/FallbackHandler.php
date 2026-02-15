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
 * Handler for unrecognized intents and fallback scenarios
 * @utterances non capisco, non ho capito, cosa dici, ripeti
 * @utterances aiuto, non so cosa fare, come si fa
 */
class FallbackHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        $appId = TombolaConfig::getAlexaAppId();
        return empty($appId) || $request->session->application->applicationId === $appId;
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest &&
            $request->request->intent->name === 'AMAZON.FallbackIntent';
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        $extractedCount = $tombola->getExtractedCount();
        
        // Build contextual fallback based on game state
        if ($extractedCount === 0) {
            $speech = $this->buildBeginnerFallback();
        } elseif ($tombola->isComplete()) {
            $speech = $this->buildCompleteFallback();
        } else {
            $speech = $this->buildGameFallback($extractedCount);
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
     * Build fallback for new players
     */
    private function buildBeginnerFallback(): string
    {
        return SpeechBuilder::create()
            ->sentence("Non ho capito. Per iniziare a giocare alla Tombola Napoletana, dimmi:")
            ->break("500ms")
            ->emphasis("'estrai un numero'", "moderate")
            ->break("1s")
            ->sentence("Oppure dimmi 'aiuto' per conoscere tutti i comandi.")
            ->build();
    }
    
    /**
     * Build fallback for active game
     */
    private function buildGameFallback(int $extractedCount): string
    {
        return SpeechBuilder::create()
            ->sentence("Non ho capito. I comandi principali sono:")
            ->break("300ms")
            ->sentence("'estrai un numero' per continuare")
            ->break("300ms")
            ->sentence("'ripeti' per sentire l'ultimo numero")
            ->break("300ms")
            ->sentence("'stato' per sapere a che punto siamo")
            ->break("300ms")
            ->sentence("'aiuto' per tutti i comandi")
            ->break("500ms")
            ->sentence("Hai già estratto {$extractedCount} numeri. Cosa vuoi fare?")
            ->build();
    }
    
    /**
     * Build fallback for completed game
     */
    private function buildCompleteFallback(): string
    {
        return SpeechBuilder::create()
            ->sentence("Non ho capito. La partita è completata!")
            ->break("300ms")
            ->sentence("Puoi iniziare una nuova partita dicendo:")
            ->break("300ms")
            ->emphasis("'nuova partita'", "moderate")
            ->break("500ms")
            ->sentence("Oppure dimmi 'aiuto' per altre opzioni.")
            ->build();
    }
    
    /**
     * Handle unrecognized requests (non-intent requests)
     */
    public function handleUnrecognizedRequest(Request $request): Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        $speech = SpeechBuilder::buildFallbackSpeech();
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => $tombola->getLastNumber(),
                'last_smorfia' => $tombola->getLastSmorfia(),
                'extracted_count' => $tombola->getExtractedCount(),
                'user_id' => $userId
            ])
            ->reprompt("Dimmi 'aiuto' per i comandi disponibili.")
            ->build();
    }
}

<?php

declare(strict_types=1);

namespace TombolaNapoletana\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Response\Response;
use TombolaNapoletana\Services\TombolaManager;
use TombolaNapoletana\Services\SpeechBuilder;
use TombolaNapoletana\Services\AutoModeService;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Handler for mode changes (auto, normal, slow)
 * @utterances auto, modalità automatica, estrazione automatica, automatico
 * @utterances estrai in automatico, modalità auto
 * @utterances modalità normale, velocità normale, veloce, normale
 * @utterances basta lento, stop lento, leggi normale, disattiva modalità lenta
 * @utterances slow, lento, modalità lenta, estrazione lenta
 * @utterances modo lento, ripeti più volte
 */
class ModeHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        $appId = TombolaConfig::getAlexaAppId();
        return empty($appId) || $request->session->application->applicationId === $appId;
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest &&
            in_array($request->request->intent->name, [
                'AutoModeIntent',
                'NormalModeIntent',
                'SlowModeIntent'
            ], true);
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        // Extract mode from intent name
        $intentName = $request->request->intent->name;
        $newMode = match($intentName) {
            'AutoModeIntent' => TombolaConfig::READING_MODE_AUTO,
            'NormalModeIntent' => TombolaConfig::READING_MODE_NORMAL,
            'SlowModeIntent' => TombolaConfig::READING_MODE_SLOW,
            default => TombolaConfig::READING_MODE_NORMAL
        };
        
        // Handle auto mode specially
        if ($newMode === TombolaConfig::READING_MODE_AUTO) {
            return $this->handleAutoMode($tombola, $userId);
        }
        
        // Handle normal and slow modes
        $tombola->setReadingMode($newMode);
        $speech = SpeechBuilder::buildModeChangeSpeech($newMode);
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $newMode,
                'last_number' => $tombola->getLastNumber(),
                'last_smorfia' => $tombola->getLastSmorfia(),
                'extracted_count' => $tombola->getExtractedCount(),
                'user_id' => $userId
            ])
            ->reprompt("Vuoi estrarre un numero?")
            ->build();
    }
    
    /**
     * Handle auto mode execution
     */
    private function handleAutoMode(TombolaManager $tombola, string $userId): Response
    {
        $autoModeService = new AutoModeService($tombola);
        
        if (!$autoModeService->canExecuteAutoMode()) {
            $speech = "Non posso eseguire la modalità automatica. " .
                     ($tombola->isComplete() ? "La partita è terminata." : "Non ci sono numeri disponibili.");
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Vuoi iniziare una nuova partita?")
                ->build();
        }
        
        // Execute auto mode
        $numbers = $autoModeService->executeAutoMode();
        
        if (empty($numbers)) {
            $speech = "Non ci sono più numeri da estrarre.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Vuoi iniziare una nuova partita?")
                ->build();
        }
        
        $speech = SpeechBuilder::buildAutoModeSpeech($numbers);
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => $tombola->getLastNumber(),
                'last_smorfia' => $tombola->getLastSmorfia(),
                'extracted_count' => $tombola->getExtractedCount(),
                'user_id' => $userId
            ])
            ->reprompt("Vuoi continuare manualmente?")
            ->build();
    }
}

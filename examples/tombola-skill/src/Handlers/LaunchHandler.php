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
 * Handler for LaunchRequest - Welcome and game resume
 * @utterances benvenuto, avvia tombola, inizia partita, apri tombola napoletana
 */
class LaunchHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        $appId = TombolaConfig::getAlexaAppId();
        return empty($appId) || $request->session->application->applicationId === $appId;
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest;
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        $extractedCount = $tombola->getExtractedCount();
        $readingMode = $tombola->getReadingMode();
        
        if ($extractedCount > 0) {
            // Resume existing game
            $lastNumber = $tombola->getLastNumber();
            $lastSmorfia = $tombola->getLastSmorfia();
            $speech = "Bentornato alla Tombola Napoletana!";
        } else {
            // New game - simplified response for testing
            $speech = "Benvenuto alla Tombola Napoletana! Sono pronto a iniziare una nuova partita.";
        }
        
        return ResponseBuilder::create()
            ->text($speech)
            ->sessionAttributes([
                'reading_mode' => $readingMode,
                'last_number' => $tombola->getLastNumber(),
                'last_smorfia' => $tombola->getLastSmorfia(),
                'extracted_count' => $extractedCount,
                'user_id' => $userId
            ])
            ->reprompt("Vuoi estrarre un numero?")
            ->keepSession()
            ->build();
    }
}

<?php

declare(strict_types=1);

namespace TombolaNapoletana\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Response\Response;
use Rboschin\AmazonAlexa\Services\PerformanceService;
use TombolaNapoletana\Services\TombolaManager;
use TombolaNapoletana\Services\SpeechBuilder;
use TombolaNapoletana\Services\SmorfiaService;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Handler for number extraction and continue intent
 * @utterances estrai un numero, prossimo numero, numero, avanti, vai
 * @utterances dammi un numero, nuovo numero, estrai, continua, prosegui
 * @utterances si, sì, ok, inizia
 */
class ExtractNumberHandler extends AbstractRequestHandler
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
                'ExtractNumberIntent',
                'ContinueIntent'
            ], true);
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        
        $tombola = new TombolaManager($userId);
        
        // Check if game is complete
        if ($tombola->isComplete()) {
                $speech = SpeechBuilder::buildGameCompleteSpeech();
                
                return ResponseBuilder::create()
                    ->ssml($speech)
                    ->sessionAttributes([
                        'reading_mode' => $tombola->getReadingMode(),
                        'extracted_count' => $tombola->getExtractedCount(),
                        'user_id' => $userId
                    ])
                    ->reprompt("Vuoi iniziare una nuova partita?")
                    ->build();
            }
            
            // Extract new number
            $number = $tombola->generateRandomNumber();
            
            if ($number === null) {
                $speech = SpeechBuilder::buildGameCompleteSpeech();
                
                return ResponseBuilder::create()
                    ->ssml($speech)
                    ->sessionAttributes([
                        'reading_mode' => $tombola->getReadingMode(),
                        'extracted_count' => $tombola->getExtractedCount(),
                        'user_id' => $userId
                    ])
                    ->reprompt("Vuoi iniziare una nuova partita?")
                    ->build();
            }
            
            // Extract the number and update session
            $tombola->extractNumber($number);
            $smorfia = SmorfiaService::getSmorfia($number);
            $readingMode = $tombola->getReadingMode();
            
            // Build speech based on reading mode
            if ($readingMode === TombolaConfig::READING_MODE_SLOW) {
                $speech = SpeechBuilder::buildSlowModeWithPrompt($number, $smorfia);
            } else {
                $speech = SpeechBuilder::buildNumberSpeech($number, $smorfia);
            }
            
            return ResponseBuilder::create()
                ->ssml($speech)
                ->sessionAttributes([
                    'reading_mode' => $readingMode,
                    'last_number' => $number,
                    'last_smorfia' => $smorfia,
                    'extracted_count' => $tombola->getExtractedCount(),
                    'user_id' => $userId
                ])
                ->reprompt("Vuoi estrarre un altro numero?")
                ->build();
    }
}

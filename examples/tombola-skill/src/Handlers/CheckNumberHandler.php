<?php

declare(strict_types=1);

namespace TombolaNapoletana\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Response\Response;
use TombolaNapoletana\Services\TombolaManager;
use TombolaNapoletana\Services\SpeechBuilder;
use TombolaNapoletana\Services\SmorfiaService;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Handler for checking if a number has been extracted
 * @utterances è uscito il {numero}, è uscito {numero}
 * @utterances è stato estratto il {numero}, è stato estratto {numero}
 * @utterances verifica il {numero}, controlla il {numero}
 * @utterances il {numero} è uscito, c'è il {numero}
 * @slots numero AMAZON.NUMBER
 */
class CheckNumberHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        $appId = TombolaConfig::getAlexaAppId();
        return empty($appId) || $request->session->application->applicationId === $appId;
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest &&
            $request->request->intent->name === 'CheckNumberIntent';
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        // Get number from slot
        $numberSlot = $request->request->intent->slots['numero'] ?? null;
        
        if ($numberSlot === null || $numberSlot->value === null) {
            $speech = "Quale numero vuoi verificare? Dimmi un numero da 1 a 90.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Quale numero devo verificare?")
                ->build();
        }
        
        $number = (int) $numberSlot->value;
        
        // Validate number range
        if ($number < 1 || $number > 90) {
            $speech = "Il numero deve essere compreso tra 1 e 90. Riprova.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Quale numero vuoi verificare?")
                ->build();
        }
        
        $isExtracted = $tombola->isNumberExtracted($number);
        $speech = SpeechBuilder::buildCheckNumberSpeech($number, $isExtracted);
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => $tombola->getLastNumber(),
                'last_smorfia' => $tombola->getLastSmorfia(),
                'extracted_count' => $tombola->getExtractedCount(),
                'user_id' => $userId
            ])
            ->reprompt("Vuoi estrarre un altro numero?")
            ->build();
    }
}

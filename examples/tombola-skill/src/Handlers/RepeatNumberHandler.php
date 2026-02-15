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
 * Handler for repeating the last extracted number
 * @utterances ripeti, ripeti l'ultimo numero, qual era l'ultimo numero
 * @utterances che numero era, ripeti il numero, di nuovo
 */
class RepeatNumberHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        $appId = TombolaConfig::getAlexaAppId();
        return empty($appId) || $request->session->application->applicationId === $appId;
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest &&
            $request->request->intent->name === 'RepeatNumberIntent';
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        $lastNumber = $tombola->getLastNumber();
        
        if ($lastNumber === null) {
            $speech = "Non ci sono ancora numeri estratti. Dici 'estrai un numero' per iniziare.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Vuoi estrarre un numero?")
                ->build();
        }
        
        $lastSmorfia = SmorfiaService::getSmorfia($lastNumber);
        $speech = SpeechBuilder::buildRepeatNumberSpeech($lastNumber, $lastSmorfia);
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => $lastNumber,
                'last_smorfia' => $lastSmorfia,
                'extracted_count' => $tombola->getExtractedCount(),
                'user_id' => $userId
            ])
            ->reprompt("Vuoi estrarre un altro numero?")
            ->build();
    }
}

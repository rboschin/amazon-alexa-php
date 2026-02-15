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
 * Handler for providing user numbers for winning verification
 * @utterances i numeri sono {numeri}, ho i numeri {numeri}
 * @utterances ecco {numeri}, controlla {numeri}, verifica {numeri}
 * @utterances numeri {numeri}, ho {numeri}
 * @slots numeri AMAZON.SearchQuery
 */
class ProvideNumbersHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        $appId = TombolaConfig::getAlexaAppId();
        return empty($appId) || $request->session->application->applicationId === $appId;
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest &&
            $request->request->intent->name === 'ProvideNumbersIntent';
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        // Get numbers from slot
        $numbersSlot = $request->request->intent->slots['numeri'] ?? null;
        
        if ($numbersSlot === null || $numbersSlot->value === null) {
            $speech = "Quali numeri vuoi verificare? Dimmi i tuoi numeri separati da virgole o spazi.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Quali sono i tuoi numeri?")
                ->build();
        }
        
        $numbersString = $numbersSlot->value;
        
        // Check if we have a pending combination check
        $sessionAttributes = $request->session->attributes ?? [];
        $pendingCombination = $sessionAttributes['pending_combination_check'] ?? null;
        
        if ($pendingCombination === null) {
            // No pending check, ask what to verify
            $speech = "Ho ricevuto i tuoi numeri. Cosa vuoi verificare? " .
                     "Puoi dire 'ambo', 'terna', 'quaterna', 'cinquina' o 'tombola'.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->sessionAttributes([
                    'reading_mode' => $tombola->getReadingMode(),
                    'last_number' => $tombola->getLastNumber(),
                    'last_smorfia' => $tombola->getLastSmorfia(),
                    'extracted_count' => $tombola->getExtractedCount(),
                    'user_id' => $userId,
                    'pending_numbers' => $numbersString
                ])
                ->reprompt("Cosa vuoi verificare con questi numeri?")
                ->build();
        }
        
        // We have a pending combination check, execute it
        try {
            $combination = $tombola->checkWinningCombination($numbersString, $pendingCombination);
            $speech = SpeechBuilder::buildWinningCombinationSpeech($combination);
            
            return ResponseBuilder::create()
                ->ssml($speech)
                ->sessionAttributes([
                    'reading_mode' => $tombola->getReadingMode(),
                    'last_number' => $tombola->getLastNumber(),
                    'last_smorfia' => $tombola->getLastSmorfia(),
                    'extracted_count' => $tombola->getExtractedCount(),
                    'user_id' => $userId,
                    'pending_combination_check' => null // Clear pending
                ])
                ->reprompt("Vuoi fare un'altra verifica?")
                ->build();
                
        } catch (\InvalidArgumentException $e) {
            $speech = "Formato numeri non valido. Per favore, dimmi i numeri separati da virgole o spazi.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->sessionAttributes([
                    'reading_mode' => $tombola->getReadingMode(),
                    'last_number' => $tombola->getLastNumber(),
                    'last_smorfia' => $tombola->getLastSmorfia(),
                    'extracted_count' => $tombola->getExtractedCount(),
                    'user_id' => $userId,
                    'pending_combination_check' => $pendingCombination
                ])
                ->reprompt("Quali sono i tuoi numeri per {$pendingCombination}?")
                ->build();
        }
    }
}

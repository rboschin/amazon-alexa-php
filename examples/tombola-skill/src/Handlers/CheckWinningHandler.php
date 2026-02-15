<?php

declare(strict_types=1);

namespace TombolaNapoletana\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Response\Response;
use TombolaNapoletana\Services\TombolaManager;
use TombolaNapoletana\Services\SpeechBuilder;
use TombolaNapoletana\Models\WinningCombination;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Handler for checking winning combinations (ambo, terna, quaterna, cinquina, tombola)
 * @utterances ambo, verifica ambo, ho fatto ambo, controlla ambo
 * @utterances terna, verifica terna, ho fatto terna, controlla terna
 * @utterances quaterna, verifica quaterna, ho fatto quaterna, controlla quaterna
 * @utterances cinquina, verifica cinquina, ho fatto cinquina, controlla cinquina
 * @utterances tombola, verifica tombola, ho fatto tombola, controlla tombola
 */
class CheckWinningHandler extends AbstractRequestHandler
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
                'VerifyAmboIntent',
                'VerifyTernaIntent',
                'VerifyQuaternaIntent',
                'VerifyCinquinaIntent',
                'VerifyTombolaIntent'
            ], true);
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        // Extract combination type from intent name
        $intentName = $request->request->intent->name;
        $combinationType = match($intentName) {
            'VerifyAmboIntent' => 'ambo',
            'VerifyTernaIntent' => 'terna',
            'VerifyQuaternaIntent' => 'quaterna',
            'VerifyCinquinaIntent' => 'cinquina',
            'VerifyTombolaIntent' => 'tombola',
            default => 'ambo'
        };
        
        // Check if we have extracted numbers
        $extractedCount = $tombola->getExtractedCount();
        if ($extractedCount === 0) {
            $speech = "Non ci sono ancora numeri estratti. Dici 'estrai un numero' per iniziare a giocare.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Vuoi estrarre un numero?")
                ->build();
        }
        
        // Get required count for this combination
        $requiredCount = TombolaConfig::WINNING_COMBINATIONS[$combinationType];
        
        if ($extractedCount < $requiredCount) {
            $speech = "Per verificare {$combinationType} servono almeno {$requiredCount} numeri estratti. " .
                     "Al momento ne abbiamo estratti solo {$extractedCount}. Continua a giocare!";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Vuoi estrarre un altro numero?")
                ->build();
        }
        
        // Ask for user numbers
        $speech = "Per verificare {$combinationType}, dimmi quali sono i tuoi numeri. " .
                 "Puoi dirmeli separati da virgole o spazi, ad esempio: 5, 12, 23, 45";
        
        // Store the combination type in session for next step
        return ResponseBuilder::create()
            ->text($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => $tombola->getLastNumber(),
                'last_smorfia' => $tombola->getLastSmorfia(),
                'extracted_count' => $tombola->getExtractedCount(),
                'user_id' => $userId,
                'pending_combination_check' => $combinationType
            ])
            ->reprompt("Quali sono i tuoi numeri per {$combinationType}?")
            ->build();
    }
    
    /**
     * Handle the actual winning combination verification
     * This would be called by a separate handler or in a follow-up intent
     */
    public function verifyCombination(string $numbersString, string $combinationType, TombolaManager $tombola): Response
    {
        try {
            $combination = $tombola->checkWinningCombination($numbersString, $combinationType);
            $speech = SpeechBuilder::buildWinningCombinationSpeech($combination);
            
            return ResponseBuilder::create()
                ->ssml($speech)
                ->sessionAttributes([
                    'reading_mode' => $tombola->getReadingMode(),
                    'last_number' => $tombola->getLastNumber(),
                    'last_smorfia' => $tombola->getLastSmorfia(),
                    'extracted_count' => $tombola->getExtractedCount(),
                    'user_id' => $tombola->getSession()->getUserId()
                ])
                ->reprompt("Vuoi fare un'altra verifica?")
                ->build();
                
        } catch (\InvalidArgumentException $e) {
            $speech = "Formato numeri non valido. Per favore, dimmi i numeri separati da virgole o spazi.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->reprompt("Quali sono i tuoi numeri?")
                ->build();
        }
    }
}

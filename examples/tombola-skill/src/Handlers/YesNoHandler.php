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
 * Handler for Yes/No intents (unified)
 * @utterances si, sì, ok, certo, va bene, perfetto
 * @utterances no, no grazie, non voglio, basta, fermati
 */
class YesNoHandler extends AbstractRequestHandler
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
                'AMAZON.YesIntent',
                'AMAZON.NoIntent',
                'AMAZON.CancelIntent',
                'AMAZON.StopIntent'
            ], true);
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        $intentName = $request->request->intent->name;
        $isYes = in_array($intentName, ['AMAZON.YesIntent'], true);
        $isNo = in_array($intentName, ['AMAZON.NoIntent', 'AMAZON.CancelIntent', 'AMAZON.StopIntent'], true);
        
        // Get session context
        $sessionAttributes = $request->session->attributes ?? [];
        $lastContext = $sessionAttributes['last_interaction_context'] ?? null;
        
        if ($isYes) {
            return $this->handleYes($tombola, $userId, $lastContext, $sessionAttributes);
        } elseif ($isNo) {
            return $this->handleNo($tombola, $userId, $lastContext, $sessionAttributes);
        }
        
        // Fallback
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
            ->reprompt("Cosa vuoi fare?")
            ->build();
    }
    
    /**
     * Handle Yes response
     */
    private function handleYes(TombolaManager $tombola, string $userId, ?string $context, array $sessionAttributes): Response
    {
        // Context-based responses
        if ($tombola->isComplete()) {
            $speech = "La partita è già completata. Vuoi iniziare una nuova partita?";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->sessionAttributes([
                    'reading_mode' => $tombola->getReadingMode(),
                    'last_number' => $tombola->getLastNumber(),
                    'last_smorfia' => $tombola->getLastSmorfia(),
                    'extracted_count' => $tombola->getExtractedCount(),
                    'user_id' => $userId
                ])
                ->reprompt("Vuoi iniziare una nuova partita?")
                ->build();
        }
        
        // Default Yes response - continue extracting
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
        
        $tombola->extractNumber($number);
        $smorfia = \TombolaNapoletana\Services\SmorfiaService::getSmorfia($number);
        $readingMode = $tombola->getReadingMode();
        
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
    
    /**
     * Handle No response
     */
    private function handleNo(TombolaManager $tombola, string $userId, ?string $context, array $sessionAttributes): Response
    {
        // Context-based No responses
        if ($context === 'game_paused') {
            $speech = "Partita in pausa. Quando vuoi riprendere, dì 'continua'.";
            
            return ResponseBuilder::create()
                ->text($speech)
                ->sessionAttributes([
                    'reading_mode' => $tombola->getReadingMode(),
                    'last_number' => $tombola->getLastNumber(),
                    'last_smorfia' => $tombola->getLastSmorfia(),
                    'extracted_count' => $tombola->getExtractedCount(),
                    'user_id' => $userId,
                    'game_paused' => true
                ])
                ->reprompt("Vuoi riprendere quando sei pronto?")
                ->build();
        }
        
        // Default No response - pause/stop
        $speech = SpeechBuilder::buildPauseSpeech();
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => $tombola->getLastNumber(),
                'last_smorfia' => $tombola->getLastSmorfia(),
                'extracted_count' => $tombola->getExtractedCount(),
                'user_id' => $userId,
                'game_paused' => true
            ])
            ->reprompt("Vuoi riprendere quando sei pronto?")
            ->build();
    }
}

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
 * Handler for game control (pause, end game, new game)
 * @utterances pausa, metti in pausa, salva e esci, salva partita
 * @utterances torno dopo, termina partita, fine partita, cancella partita
 * @utterances vai a casa, partita finita, abbandona, chiudi partita
 * @utterances nuova partita, ricomincia, restart, inizia nuova partita
 * @utterances nuova tombola, ricomincia da capo
 */
class GameControlHandler extends AbstractRequestHandler
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
                'PauseIntent',
                'EndGameIntent',
                'NewGameIntent'
            ], true);
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        // Extract action from intent name
        $intentName = $request->request->intent->name;
        
        return match($intentName) {
            'PauseIntent' => $this->handlePause($tombola, $userId),
            'EndGameIntent' => $this->handleEndGame($tombola, $userId),
            'NewGameIntent' => $this->handleNewGame($tombola, $userId),
            default => $this->handleUnknown($tombola, $userId)
        };
    }
    
    /**
     * Handle pause intent
     */
    private function handlePause(TombolaManager $tombola, string $userId): Response
    {
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
    
    /**
     * Handle end game intent
     */
    private function handleEndGame(TombolaManager $tombola, string $userId): Response
    {
        $speech = SpeechBuilder::buildEndGameSpeech();
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => null,
                'last_smorfia' => null,
                'extracted_count' => 0,
                'user_id' => $userId,
                'game_ended' => true
            ])
            ->reprompt("Vuoi iniziare una nuova partita?")
            ->build();
    }
    
    /**
     * Handle new game intent
     */
    private function handleNewGame(TombolaManager $tombola, string $userId): Response
    {
        // Reset the game
        $tombola->resetGame();
        
        $speech = SpeechBuilder::buildNewGameSpeech();
        
        return ResponseBuilder::create()
            ->ssml($speech)
            ->sessionAttributes([
                'reading_mode' => $tombola->getReadingMode(),
                'last_number' => null,
                'last_smorfia' => null,
                'extracted_count' => 0,
                'user_id' => $userId,
                'game_reset' => true
            ])
            ->reprompt("Vuoi estrarre il primo numero?")
            ->build();
    }
    
    /**
     * Handle unknown action
     */
    private function handleUnknown(TombolaManager $tombola, string $userId): Response
    {
        $speech = "Non ho capito l'azione. Puoi mettere in pausa, terminare o iniziare una nuova partita.";
        
        return ResponseBuilder::create()
            ->text($speech)
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
}

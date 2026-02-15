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
 * Handler for game status and statistics
 * @utterances stato, situazione, quanti numeri, quanti ne ho estratti
 * @utterances a che punto siamo, riepilogo, statistiche
 */
class StatusHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        $appId = TombolaConfig::getAlexaAppId();
        return empty($appId) || $request->session->application->applicationId === $appId;
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest &&
            $request->request->intent->name === 'StatusIntent';
    }

    protected function handleRequestInternal(Request $request): ?Response
    {
        $userId = $request->session->user->userId;
        $tombola = new TombolaManager($userId);
        
        // Get game status
        $status = $tombola->getGameStatus();
        $statistics = $tombola->getStatistics();
        
        // Build detailed status speech
        $extractedCount = $status['extracted_count'];
        $availableCount = $status['available_count'];
        $percentage = $statistics['percentage_complete'];
        
        $ssml = "Stato della partita: <break time=\"300ms\"/> " .
                "Numeri estratti: {$extractedCount} su 90. <break time=\"300ms\"/> " .
                "Ancora disponibili: {$availableCount}. <break time=\"300ms\"/> " .
                "Completamento: {$percentage} percento.";
        
        if ($status['last_number']) {
            $ssml .= " <break time=\"300ms\"/> Ultimo numero: {$status['last_number']}.";
            if ($status['last_smorfia']) {
                $ssml .= " Nella smorfia: {$status['last_smorfia']}.";
            }
        }
        
        $ssml .= " <break time=\"500ms\"/> Modalità lettura: {$status['reading_mode']}.";
        
        // Add additional context
        if ($extractedCount === 0) {
            $ssml .= " <break time=\"500ms\"/> La partita non è ancora iniziata. Dici 'estrai un numero' per cominciare.";
        } elseif ($status['is_complete']) {
            $ssml .= " <break time=\"500ms\"/> La partita è completata! Puoi iniziare una nuova partita.";
        } else {
            $ssml .= " <break time=\"500ms\"/> Vuoi estrarre un altro numero?";
        }
        
        return ResponseBuilder::create()
            ->ssml($ssml)
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

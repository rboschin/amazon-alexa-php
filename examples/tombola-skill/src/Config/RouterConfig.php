<?php

declare(strict_types=1);

namespace TombolaNapoletana\Config;

use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;
use TombolaNapoletana\Handlers\LaunchHandler;
use TombolaNapoletana\Handlers\ExtractNumberHandler;
use TombolaNapoletana\Handlers\RepeatNumberHandler;
use TombolaNapoletana\Handlers\CheckNumberHandler;
use TombolaNapoletana\Handlers\CheckWinningHandler;
use TombolaNapoletana\Handlers\ModeHandler;
use TombolaNapoletana\Handlers\GameControlHandler;
use TombolaNapoletana\Handlers\StatusHandler;
use TombolaNapoletana\Handlers\ProvideNumbersHandler;
use TombolaNapoletana\Handlers\HelpHandler;
use TombolaNapoletana\Handlers\YesNoHandler;
use TombolaNapoletana\Handlers\FallbackHandler;

/**
 * Configuration for IntentRouter with all handlers
 */
class RouterConfig
{
    /**
     * Create and configure the intent router
     */
    public static function createRouter(): IntentRouter
    {
        return (new IntentRouter())
            // Launch handler
            ->onLaunch(new LaunchHandler())
            
            // Main game handlers
            ->onIntent('ExtractNumberIntent', new ExtractNumberHandler())
            ->onIntent('ContinueIntent', new ExtractNumberHandler())  // Gestito insieme
            ->onIntent('RepeatNumberIntent', new RepeatNumberHandler())
            ->onIntent('CheckNumberIntent', new CheckNumberHandler())
            
            // Winning combinations (unified handler)
            ->onIntent('VerifyAmboIntent', new CheckWinningHandler())
            ->onIntent('VerifyTernaIntent', new CheckWinningHandler())
            ->onIntent('VerifyQuaternaIntent', new CheckWinningHandler())
            ->onIntent('VerifyCinquinaIntent', new CheckWinningHandler())
            ->onIntent('VerifyTombolaIntent', new CheckWinningHandler())
            
            // Mode handlers (unified)
            ->onIntent('AutoModeIntent', new ModeHandler())
            ->onIntent('NormalModeIntent', new ModeHandler())
            ->onIntent('SlowModeIntent', new ModeHandler())
            
            // Game control handlers (unified)
            ->onIntent('PauseIntent', new GameControlHandler())
            ->onIntent('EndGameIntent', new GameControlHandler())
            ->onIntent('NewGameIntent', new GameControlHandler())
            
            // Other handlers
            ->onIntent('StatusIntent', new StatusHandler())
            ->onIntent('ProvideNumbersIntent', new ProvideNumbersHandler())
            
            // Amazon built-in intents
            ->onIntent('AMAZON.YesIntent', new YesNoHandler())
            ->onIntent('AMAZON.NoIntent', new YesNoHandler())
            ->onIntent('AMAZON.CancelIntent', new YesNoHandler())  // Gestito come No
            ->onIntent('AMAZON.StopIntent', new YesNoHandler())    // Gestito come No
            ->onIntent('AMAZON.ResumeIntent', new ExtractNumberHandler());  // Resume = continue
    }
    
    /**
     * Get all supported intent names
     */
    public static function getSupportedIntents(): array
    {
        return [
            // Launch
            'LaunchRequest',
            
            // Main game
            'ExtractNumberIntent',
            'ContinueIntent',
            'RepeatNumberIntent',
            'CheckNumberIntent',
            
            // Winning combinations
            'VerifyAmboIntent',
            'VerifyTernaIntent',
            'VerifyQuaternaIntent',
            'VerifyCinquinaIntent',
            'VerifyTombolaIntent',
            
            // Modes
            'AutoModeIntent',
            'NormalModeIntent',
            'SlowModeIntent',
            
            // Game control
            'PauseIntent',
            'EndGameIntent',
            'NewGameIntent',
            
            // Other
            'StatusIntent',
            'ProvideNumbersIntent',
            
            // Amazon built-in
            'AMAZON.YesIntent',
            'AMAZON.NoIntent',
            'AMAZON.CancelIntent',
            'AMAZON.StopIntent',
            'AMAZON.ResumeIntent',
            'AMAZON.HelpIntent',
            'AMAZON.FallbackIntent'
        ];
    }
    
    /**
     * Get handler class for intent
     */
    public static function getHandlerForIntent(string $intentName): ?string
    {
        $handlerMap = [
            'LaunchRequest' => LaunchHandler::class,
            'ExtractNumberIntent' => ExtractNumberHandler::class,
            'ContinueIntent' => ExtractNumberHandler::class,
            'RepeatNumberIntent' => RepeatNumberHandler::class,
            'CheckNumberIntent' => CheckNumberHandler::class,
            'VerifyAmboIntent' => CheckWinningHandler::class,
            'VerifyTernaIntent' => CheckWinningHandler::class,
            'VerifyQuaternaIntent' => CheckWinningHandler::class,
            'VerifyCinquinaIntent' => CheckWinningHandler::class,
            'VerifyTombolaIntent' => CheckWinningHandler::class,
            'AutoModeIntent' => ModeHandler::class,
            'NormalModeIntent' => ModeHandler::class,
            'SlowModeIntent' => ModeHandler::class,
            'PauseIntent' => GameControlHandler::class,
            'EndGameIntent' => GameControlHandler::class,
            'NewGameIntent' => GameControlHandler::class,
            'StatusIntent' => StatusHandler::class,
            'ProvideNumbersIntent' => ProvideNumbersHandler::class,
            'AMAZON.YesIntent' => YesNoHandler::class,
            'AMAZON.NoIntent' => YesNoHandler::class,
            'AMAZON.CancelIntent' => YesNoHandler::class,
            'AMAZON.StopIntent' => YesNoHandler::class,
            'AMAZON.ResumeIntent' => ExtractNumberHandler::class,
            'AMAZON.HelpIntent' => HelpHandler::class,
            'AMAZON.FallbackIntent' => FallbackHandler::class
        ];
        
        return $handlerMap[$intentName] ?? null;
    }
}

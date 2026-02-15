<?php

declare(strict_types=1);

namespace TombolaNapoletana\Services;

use Rboschin\AmazonAlexa\Helper\SsmlGenerator;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Service for building speech responses with SSML
 */
class SpeechBuilder
{
    /**
     * Build welcome speech for new game
     */
    public static function buildWelcomeSpeech(): string
    {
        return SsmlGenerator::create()
            ->say("Benvenuto alla Tombola Napoletana!")
            ->pauseTime('500ms')
            ->say("Sono pronto a iniziare una nuova partita.")
            ->pauseTime('500ms')
            ->say("Dici 'estrai un numero' per iniziare, oppure 'aiuto' per conoscere tutti i comandi.")
            ->getSsml();
    }
    
    /**
     * Build welcome back speech for resuming game
     */
    public static function buildWelcomeBackSpeech(int $extractedCount, ?int $lastNumber, ?string $lastSmorfia): string
    {
        $ssml = SsmlGenerator::create()
            ->sentence("Bentornato alla Tombola Napoletana!")
            ->break("500ms");
        
        if ($extractedCount > 0) {
            $ssml->sentence("Abbiamo già estratto {$extractedCount} numeri.");
            
            if ($lastNumber && $lastSmorfia) {
                $ssml->break("500ms")
                    ->sentence("L'ultimo numero è stato il {$lastNumber}")
                    ->break("300ms")
                    ->sentence("Nella smorfia: {$lastSmorfia}");
            }
        }
        
        $ssml->break("500ms")
            ->sentence("Vuoi continuare con l'estrazione?")
            ->build();
        
        return $ssml->build();
    }
    
    /**
     * Build number extraction speech
     */
    public static function buildNumberSpeech(int $number, string $smorfia): string
    {
        return SsmlGenerator::create()
            ->sentence("Il numero estratto è:")
            ->break("500ms")
            ->emphasis((string)$number, "strong")
            ->break("1s")
            ->sentence("Cifre separate:")
            ->break("300ms")
            ->sayAs(implode(", ", str_split((string)$number)), "characters")
            ->break("1s")
            ->sentence("Nella smorfia napoletana:")
            ->break("300ms")
            ->prosody($smorfia, "slow", "+10%")
            ->break("1s")
            ->sentence("Continua o aiuto per i comandi.")
            ->build();
    }
    
    /**
     * Build slow mode speech with prompt
     */
    public static function buildSlowModeWithPrompt(int $number, string $smorfia): string
    {
        return SsmlGenerator::create()
            ->sentence("Modalità lenta attivata.")
            ->break("500ms")
            ->sentence("Il numero estratto è:")
            ->break("500ms")
            ->emphasis((string)$number, "strong")
            ->break("1s")
            ->sentence("Ripeto: {$number}")
            ->break("500ms")
            ->sentence("Cifre separate:")
            ->break("300ms")
            ->sayAs(implode(", ", str_split((string)$number)), "characters")
            ->break("1s")
            ->sentence("Nella smorfia napoletana:")
            ->break("300ms")
            ->prosody($smorfia, "slow", "+10%")
            ->break("500ms")
            ->sentence("Ripeto ancora: {$number}, {$smorfia}")
            ->break("1s")
            ->sentence("Vuoi che estragga il prossimo numero?")
            ->build();
    }
    
    /**
     * Build repeat number speech
     */
    public static function buildRepeatNumberSpeech(int $number, string $smorfia): string
    {
        return SsmlGenerator::create()
            ->sentence("L'ultimo numero estratto è:")
            ->break("500ms")
            ->emphasis((string)$number, "strong")
            ->break("1s")
            ->sentence("Nella smorfia napoletana:")
            ->break("300ms")
            ->prosody($smorfia, "slow", "+10%")
            ->build();
    }
    
    /**
     * Build check number speech
     */
    public static function buildCheckNumberSpeech(int $number, bool $isExtracted): string
    {
        if ($isExtracted) {
            return SsmlGenerator::create()
                ->sentence("Sì, il numero {$number} è già stato estratto.")
                ->break("500ms")
                ->sentence("Nella smorfia: " . SmorfiaService::getSmorfia($number))
                ->build();
        } else {
            return SsmlGenerator::create()
                ->sentence("No, il numero {$number} non è ancora stato estratto.")
                ->break("500ms")
                ->sentence("È ancora disponibile.")
                ->build();
        }
    }
    
    /**
     * Build winning combination speech
     */
    public static function buildWinningCombinationSpeech(WinningCombination $combination): string
    {
        $speech = $combination->formatForSpeech();
        
        return SsmlGenerator::create()
            ->sentence($speech)
            ->break("500ms")
            ->sentence("Vuoi verificare un'altra combinazione?")
            ->build();
    }
    
    /**
     * Build help speech
     */
    public static function buildHelpSpeech(): string
    {
        return SsmlGenerator::create()
            ->sentence("Ecco i comandi disponibili per la Tombola Napoletana:")
            ->break("500ms")
            ->sentence("Per giocare: 'estrai un numero', 'prossimo numero', 'numero', 'avanti'")
            ->break("300ms")
            ->sentence("Per ripetere: 'ripeti', 'ripeti il numero'")
            ->break("300ms")
            ->sentence("Per verificare: 'è uscito il numero', seguito dal numero")
            ->break("300ms")
            ->sentence("Per vincite: 'ambo', 'terna', 'quaterna', 'cinquina', 'tombola'")
            ->break("300ms")
            ->sentence("Per modalità: 'modalità automatica', 'modalità lenta', 'modalità normale'")
            ->break("300ms")
            ->sentence("Per controllo partita: 'stato', 'pausa', 'nuova partita', 'termina partita'")
            ->break("500ms")
            ->sentence("Cosa vuoi fare?")
            ->build();
    }
    
    /**
     * Build status speech
     */
    public static function buildStatusSpeech(array $status): string
    {
        $extractedCount = $status['extracted_count'];
        $availableCount = $status['available_count'];
        $percentage = round(($extractedCount / TombolaConfig::MAX_NUMBER) * 100, 1);
        
        $ssml = SsmlGenerator::create()
            ->sentence("Stato della partita:")
            ->break("300ms")
            ->sentence("Numeri estratti: {$extractedCount} su 90")
            ->break("300ms")
            ->sentence("Ancora disponibili: {$availableCount}")
            ->break("300ms")
            ->sentence("Completamento: {$percentage} percento");
        
        if ($status['last_number']) {
            $ssml->break("300ms")
                ->sentence("Ultimo numero: {$status['last_number']}");
        }
        
        $ssml->break("500ms")
            ->sentence("Modalità lettura: " . $status['reading_mode'])
            ->build();
        
        return $ssml->build();
    }
    
    /**
     * Build game complete speech
     */
    public static function buildGameCompleteSpeech(): string
    {
        return SsmlGenerator::create()
            ->sentence("Complimenti! Abbiamo estratto tutti i 90 numeri!")
            ->break("500ms")
            ->sentence("La partita è terminata.")
            ->break("500ms")
            ->sentence("Puoi iniziare una nuova partita dicendo 'nuova partita'.")
            ->build();
    }
    
    /**
     * Build auto mode speech
     */
    public static function buildAutoModeSpeech(array $numbers): string
    {
        $ssml = SsmlGenerator::create()
            ->sentence("Modalità automatica attivata.")
            ->break("500ms")
            ->sentence("Estraggo " . count($numbers) . " numeri in sequenza:")
            ->break("1s");
        
        foreach ($numbers as $index => $data) {
            $number = $data['number'];
            $smorfia = $data['smorfia'];
            
            $ssml->sentence("Numero " . ($index + 1) . ":")
                ->break("300ms")
                ->emphasis((string)$number, "strong")
                ->break("500ms")
                ->prosody($smorfia, "slow", "+5%")
                ->break("800ms");
        }
        
        $ssml->sentence("Estrazione automatica completata.")
            ->break("500ms")
            ->sentence("Vuoi continuare manualmente?")
            ->build();
        
        return $ssml->build();
    }
    
    /**
     * Build mode change speech
     */
    public static function buildModeChangeSpeech(string $mode): string
    {
        $modeDescription = match($mode) {
            TombolaConfig::READING_MODE_NORMAL => "normale",
            TombolaConfig::READING_MODE_SLOW => "lenta con ripetizioni",
            TombolaConfig::READING_MODE_AUTO => "automatica",
            default => "sconosciuta"
        };
        
        return SsmlGenerator::create()
            ->sentence("Modalità di lettura impostata su: {$modeDescription}")
            ->break("500ms")
            ->sentence("Il prossimo numero sarà letto in questa modalità.")
            ->build();
    }
    
    /**
     * Build pause speech
     */
    public static function buildPauseSpeech(): string
    {
        return SsmlGenerator::create()
            ->sentence("Partita messa in pausa.")
            ->break("500ms")
            ->sentence("Quando vuoi riprendere, dì 'continua' o 'riprendi la partita'.")
            ->build();
    }
    
    /**
     * Build new game speech
     */
    public static function buildNewGameSpeech(): string
    {
        return SsmlGenerator::create()
            ->sentence("Nuova partita avviata!")
            ->break("500ms")
            ->sentence("Tutti i numeri sono stati resettati.")
            ->break("500ms")
            ->sentence("Dici 'estrai un numero' per iniziare.")
            ->build();
    }
    
    /**
     * Build end game speech
     */
    public static function buildEndGameSpeech(): string
    {
        return SsmlGenerator::create()
            ->sentence("Partita terminata.")
            ->break("500ms")
            ->sentence("Grazie per aver giocato alla Tombola Napoletana!")
            ->break("500ms")
            ->sentence("Puoi iniziare una nuova partita quando vuoi.")
            ->build();
    }
    
    /**
     * Build error speech
     */
    public static function buildErrorSpeech(string $message): string
    {
        return SsmlGenerator::create()
            ->sentence("Mi dispiace, si è verificato un errore.")
            ->break("300ms")
            ->sentence($message)
            ->break("500ms")
            ->sentence("Riprova o dici 'aiuto' per i comandi disponibili.")
            ->build();
    }
    
    /**
     * Build fallback speech
     */
    public static function buildFallbackSpeech(): string
    {
        return SsmlGenerator::create()
            ->sentence("Non ho capito.")
            ->break("300ms")
            ->sentence("Puoi dire 'aiuto' per conoscere tutti i comandi disponibili.")
            ->build();
    }
}

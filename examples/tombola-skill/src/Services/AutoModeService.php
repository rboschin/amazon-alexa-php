<?php

declare(strict_types=1);

namespace TombolaNapoletana\Services;

use TombolaNapoletana\Config\TombolaConfig;

/**
 * Service for handling auto mode functionality
 */
class AutoModeService
{
    private TombolaManager $tombolaManager;
    
    public function __construct(TombolaManager $tombolaManager)
    {
        $this->tombolaManager = $tombolaManager;
    }
    
    /**
     * Execute auto mode extraction
     */
    public function executeAutoMode(int $count = null): array
    {
        $count = $count ?? TombolaConfig::getAutoModeCount();
        
        // Limit count to available numbers
        $availableCount = count($this->tombolaManager->getAvailableNumbers());
        $actualCount = min($count, $availableCount);
        
        if ($actualCount === 0) {
            return [];
        }
        
        return $this->tombolaManager->generateAutoModeNumbers($actualCount);
    }
    
    /**
     * Check if auto mode can be executed
     */
    public function canExecuteAutoMode(): bool
    {
        return !$this->tombolaManager->isComplete() 
            && count($this->tombolaManager->getAvailableNumbers()) > 0;
    }
    
    /**
     * Get remaining auto mode extractions possible
     */
    public function getRemainingExtractions(): int
    {
        return count($this->tombolaManager->getAvailableNumbers());
    }
    
    /**
     * Format auto mode results for speech
     */
    public function formatAutoModeResults(array $numbers): string
    {
        if (empty($numbers)) {
            return "Non ci sono più numeri da estrarre.";
        }
        
        $speech = "Estrazione automatica di " . count($numbers) . " numeri: ";
        
        foreach ($numbers as $index => $data) {
            $number = $data['number'];
            $smorfia = $data['smorfia'];
            
            $speech .= "Numero " . ($index + 1) . ": {$number} ({$smorfia})";
            
            if ($index < count($numbers) - 1) {
                $speech .= ", ";
            }
        }
        
        return $speech;
    }
}

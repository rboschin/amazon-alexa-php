<?php

declare(strict_types=1);

namespace TombolaNapoletana\Models;

use TombolaNapoletana\Config\TombolaConfig;

/**
 * Model for winning combinations in Tombola
 */
class WinningCombination
{
    private string $type;
    private array $numbers;
    private array $extractedNumbers;
    private bool $isWinner;
    private ?array $missingNumbers = null;
    
    public function __construct(string $type, array $numbers, array $extractedNumbers)
    {
        $this->type = $type;
        $this->numbers = $numbers;
        $this->extractedNumbers = $extractedNumbers;
        $this->isWinner = $this->checkWinning();
    }
    
    /**
     * Check if the combination is a winner
     */
    private function checkWinning(): bool
    {
        $requiredCount = TombolaConfig::WINNING_COMBINATIONS[$this->type] ?? 0;
        
        if (count($this->numbers) < $requiredCount) {
            return false;
        }
        
        $matchedNumbers = array_intersect($this->numbers, $this->extractedNumbers);
        
        return count($matchedNumbers) >= $requiredCount;
    }
    
    /**
     * Get missing numbers to complete the combination
     */
    public function getMissingNumbers(): array
    {
        if ($this->missingNumbers === null) {
            $requiredCount = TombolaConfig::WINNING_COMBINATIONS[$this->type] ?? 0;
            $matchedNumbers = array_intersect($this->numbers, $this->extractedNumbers);
            
            if (count($matchedNumbers) >= $requiredCount) {
                $this->missingNumbers = [];
            } else {
                $needed = $requiredCount - count($matchedNumbers);
                $availableNumbers = array_diff($this->numbers, $this->extractedNumbers);
                $this->missingNumbers = array_slice($availableNumbers, 0, $needed);
            }
        }
        
        return $this->missingNumbers;
    }
    
    /**
     * Get matched numbers
     */
    public function getMatchedNumbers(): array
    {
        return array_intersect($this->numbers, $this->extractedNumbers);
    }
    
    /**
     * Get count of matched numbers
     */
    public function getMatchedCount(): int
    {
        return count($this->getMatchedNumbers());
    }
    
    /**
     * Get required count for this combination type
     */
    public function getRequiredCount(): int
    {
        return TombolaConfig::WINNING_COMBINATIONS[$this->type] ?? 0;
    }
    
    /**
     * Format result for speech
     */
    public function formatForSpeech(): string
    {
        if ($this->isWinner()) {
            $matchedCount = $this->getMatchedCount();
            $requiredCount = $this->getRequiredCount();
            
            return "Complimenti! Hai fatto {$this->type} con {$matchedCount} numeri su {$requiredCount}!";
        } else {
            $matchedCount = $this->getMatchedCount();
            $requiredCount = $this->getRequiredCount();
            $missing = $this->getMissingNumbers();
            
            $speech = "Per {$this->type} hai {$matchedCount} numeri su {$requiredCount}. ";
            
            if (!empty($missing)) {
                $speech .= "Ti mancano ancora " . implode(', ', $missing);
            }
            
            return $speech;
        }
    }
    
    // Getters
    public function getType(): string
    {
        return $this->type;
    }
    
    public function getNumbers(): array
    {
        return $this->numbers;
    }
    
    public function getExtractedNumbers(): array
    {
        return $this->extractedNumbers;
    }
    
    public function isWinner(): bool
    {
        return $this->isWinner;
    }
    
    /**
     * Create winning combination from user numbers string
     */
    public static function fromUserNumbers(string $numbersString, array $extractedNumbers, string $type): self
    {
        // Parse numbers from string (comma, space, or "e" separated)
        $numbers = preg_split('/[,\\s]+|\\be\\b/i', $numbersString);
        $numbers = array_filter($numbers, 'is_numeric');
        $numbers = array_map('intval', $numbers);
        $numbers = array_unique($numbers);
        $numbers = array_filter($numbers, fn($n) => $n >= 1 && $n <= 90);
        
        return new self($type, array_values($numbers), $extractedNumbers);
    }
    
    /**
     * Validate combination type
     */
    public static function isValidType(string $type): bool
    {
        return isset(TombolaConfig::WINNING_COMBINATIONS[$type]);
    }
    
    /**
     * Get all valid combination types
     */
    public static function getValidTypes(): array
    {
        return array_keys(TombolaConfig::WINNING_COMBINATIONS);
    }
}

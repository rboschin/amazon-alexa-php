<?php

declare(strict_types=1);

namespace TombolaNapoletana\Services;

use TombolaNapoletana\Models\TombolaSession;
use TombolaNapoletana\Models\WinningCombination;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Main business logic service for Tombola Napoletana
 */
class TombolaManager
{
    private string $userId;
    private ?TombolaSession $session = null;
    
    public function __construct(string $userId)
    {
        $this->userId = $userId;
        $this->loadOrCreateSession();
    }
    
    /**
     * Load existing session or create new one
     */
    private function loadOrCreateSession(): void
    {
        $this->session = TombolaSession::load($this->userId);
        
        if ($this->session === null) {
            $this->session = new TombolaSession($this->userId);
            $this->session->save();
        }
    }
    
    /**
     * Ensure session is initialized
     */
    private function ensureSession(): void
    {
        if ($this->session === null) {
            $this->loadOrCreateSession();
        }
    }
    
    /**
     * Generate a new random number
     */
    public function generateRandomNumber(): ?int
    {
        $this->ensureSession();
        
        if ($this->session->isComplete()) {
            return null;
        }
        
        $availableNumbers = $this->session->getAvailableNumbers();
        
        if (empty($availableNumbers)) {
            return null;
        }
        
        $randomKey = array_rand($availableNumbers);
        $number = $availableNumbers[$randomKey];
        
        return $number;
    }
    
    /**
     * Extract a number and update session
     */
    public function extractNumber(int $number): void
    {
        if ($this->session->isNumberExtracted($number)) {
            throw new \InvalidArgumentException("Number {$number} is already extracted");
        }
        
        $smorfia = SmorfiaService::getSmorfia($number);
        $this->session->addExtractedNumber($number, $smorfia);
        $this->session->save();
    }
    
    /**
     * Check if a number has been extracted
     */
    public function isNumberExtracted(int $number): bool
    {
        return $this->session->isNumberExtracted($number);
    }
    
    /**
     * Get last extracted number
     */
    public function getLastNumber(): ?int
    {
        return $this->session->getLastNumber();
    }
    
    /**
     * Get last extracted smorfia
     */
    public function getLastSmorfia(): ?string
    {
        return $this->session->getLastSmorfia();
    }
    
    /**
     * Get count of extracted numbers
     */
    public function getExtractedCount(): int
    {
        return $this->session->getExtractedCount();
    }
    
    /**
     * Get all extracted numbers
     */
    public function getExtractedNumbers(): array
    {
        return $this->session->getExtractedNumbers();
    }
    
    /**
     * Check if game is complete
     */
    public function isComplete(): bool
    {
        return $this->session->isComplete();
    }
    
    /**
     * Get available numbers
     */
    public function getAvailableNumbers(): array
    {
        return $this->session->getAvailableNumbers();
    }
    
    /**
     * Get reading mode
     */
    public function getReadingMode(): string
    {
        return $this->session->getReadingMode();
    }
    
    /**
     * Set reading mode
     */
    public function setReadingMode(string $mode): void
    {
        $this->session->setReadingMode($mode);
        $this->session->save();
    }
    
    /**
     * Check winning combination
     */
    public function checkWinningCombination(string $numbersString, string $type): WinningCombination
    {
        if (!WinningCombination::isValidType($type)) {
            throw new \InvalidArgumentException("Invalid winning combination type: {$type}");
        }
        
        return WinningCombination::fromUserNumbers(
            $numbersString,
            $this->session->getExtractedNumbers(),
            $type
        );
    }
    
    /**
     * Save user numbers for verification
     */
    public function saveUserNumbers(string $numbersString): void
    {
        $db = DatabaseService::getInstance();
        
        $db->execute(
            'INSERT INTO user_numbers (session_id, numbers, created_at) VALUES (?, ?, ?)',
            [
                $this->session->getId(),
                $numbersString,
                (new \DateTime())->format('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Get game status
     */
    public function getGameStatus(): array
    {
        return [
            'extracted_count' => $this->getExtractedCount(),
            'last_number' => $this->getLastNumber(),
            'last_smorfia' => $this->getLastSmorfia(),
            'reading_mode' => $this->getReadingMode(),
            'is_complete' => $this->isComplete(),
            'available_count' => count($this->getAvailableNumbers())
        ];
    }
    
    /**
     * Reset game (new game)
     */
    public function resetGame(): void
    {
        $this->session = new TombolaSession($this->userId);
        $this->session->save();
    }
    
    /**
     * Get session
     */
    public function getSession(): TombolaSession
    {
        return $this->session;
    }
    
    /**
     * Generate multiple numbers for auto mode
     */
    public function generateAutoModeNumbers(int $count = 5): array
    {
        $numbers = [];
        $maxCount = min($count, count($this->session->getAvailableNumbers()));
        
        for ($i = 0; $i < $maxCount; $i++) {
            $number = $this->generateRandomNumber();
            if ($number !== null) {
                $this->extractNumber($number);
                $numbers[] = [
                    'number' => $number,
                    'smorfia' => SmorfiaService::getSmorfia($number)
                ];
            }
        }
        
        return $numbers;
    }
    
    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $extractedNumbers = $this->session->getExtractedNumbers();
        
        return [
            'total_extracted' => count($extractedNumbers),
            'remaining' => count($this->session->getAvailableNumbers()),
            'percentage_complete' => round((count($extractedNumbers) / TombolaConfig::MAX_NUMBER) * 100, 1),
            'last_number' => $this->getLastNumber(),
            'last_smorfia' => $this->getLastSmorfia(),
            'reading_mode' => $this->getReadingMode(),
            'session_created' => $this->session->getCreatedAt()->format('Y-m-d H:i:s'),
            'session_updated' => $this->session->getUpdatedAt()->format('Y-m-d H:i:s')
        ];
    }
}

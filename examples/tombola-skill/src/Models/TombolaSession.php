<?php

declare(strict_types=1);

namespace TombolaNapoletana\Models;

use TombolaNapoletana\Services\DatabaseService;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Tombola session model
 * Represents a user's game session with extracted numbers and state
 */
class TombolaSession
{
    private ?int $id = null;
    private string $userId;
    private array $extractedNumbers = [];
    private ?int $lastNumber = null;
    private ?string $lastSmorfia = null;
    private string $readingMode = TombolaConfig::READING_MODE_NORMAL;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    
    public function __construct(string $userId)
    {
        $this->userId = $userId;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
    
    /**
     * Load session from database
     */
    public static function load(string $userId): ?self
    {
        $db = DatabaseService::getInstance();
        
        $stmt = $db->execute(
            'SELECT * FROM sessions WHERE user_id = ?',
            [$userId]
        );
        
        $data = $stmt->fetch();
        
        if (!$data) {
            return null;
        }
        
        $session = new self($data['user_id']);
        $session->id = (int) $data['id'];
        $session->extractedNumbers = json_decode($data['extracted_numbers'], true) ?? [];
        $session->lastNumber = $data['last_number'] ? (int) $data['last_number'] : null;
        $session->lastSmorfia = $data['last_smorfia'];
        $session->readingMode = $data['reading_mode'];
        $session->createdAt = new \DateTime($data['created_at']);
        $session->updatedAt = new \DateTime($data['updated_at']);
        
        return $session;
    }
    
    /**
     * Create or update session in database
     */
    public function save(): void
    {
        $db = DatabaseService::getInstance();
        $this->updatedAt = new \DateTime();
        
        if ($this->id === null) {
            // Insert new session
            $stmt = $db->execute(
                'INSERT INTO sessions (user_id, extracted_numbers, last_number, last_smorfia, reading_mode, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $this->userId,
                    json_encode($this->extractedNumbers),
                    $this->lastNumber,
                    $this->lastSmorfia,
                    $this->readingMode,
                    $this->createdAt->format('Y-m-d H:i:s'),
                    $this->updatedAt->format('Y-m-d H:i:s')
                ]
            );
            $this->id = (int) $db->lastInsertId();
        } else {
            // Update existing session
            $db->execute(
                'UPDATE sessions SET extracted_numbers = ?, last_number = ?, last_smorfia = ?, reading_mode = ?, updated_at = ? 
                 WHERE id = ?',
                [
                    json_encode($this->extractedNumbers),
                    $this->lastNumber,
                    $this->lastSmorfia,
                    $this->readingMode,
                    $this->updatedAt->format('Y-m-d H:i:s'),
                    $this->id
                ]
            );
        }
    }
    
    /**
     * Add extracted number to session
     */
    public function addExtractedNumber(int $number, string $smorfia): void
    {
        $this->extractedNumbers[] = $number;
        $this->lastNumber = $number;
        $this->lastSmorfia = $smorfia;
        
        // Save to game_numbers table
        $db = DatabaseService::getInstance();
        $db->execute(
            'INSERT INTO game_numbers (session_id, number, smorfia, extracted_at) VALUES (?, ?, ?, ?)',
            [$this->id, $number, $smorfia, (new \DateTime())->format('Y-m-d H:i:s')]
        );
    }
    
    /**
     * Check if number has been extracted
     */
    public function isNumberExtracted(int $number): bool
    {
        return in_array($number, $this->extractedNumbers, true);
    }
    
    /**
     * Get available numbers (not yet extracted)
     */
    public function getAvailableNumbers(): array
    {
        return array_diff(
            range(TombolaConfig::MIN_NUMBER, TombolaConfig::MAX_NUMBER),
            $this->extractedNumbers
        );
    }
    
    /**
     * Check if game is complete (all numbers extracted)
     */
    public function isComplete(): bool
    {
        return count($this->extractedNumbers) >= TombolaConfig::MAX_NUMBER;
    }
    
    /**
     * Get count of extracted numbers
     */
    public function getExtractedCount(): int
    {
        return count($this->extractedNumbers);
    }
    
    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getUserId(): string
    {
        return $this->userId;
    }
    
    public function getExtractedNumbers(): array
    {
        return $this->extractedNumbers;
    }
    
    public function setExtractedNumbers(array $numbers): void
    {
        $this->extractedNumbers = $numbers;
    }
    
    public function getLastNumber(): ?int
    {
        return $this->lastNumber;
    }
    
    public function getLastSmorfia(): ?string
    {
        return $this->lastSmorfia;
    }
    
    public function getReadingMode(): string
    {
        return $this->readingMode;
    }
    
    public function setReadingMode(string $mode): void
    {
        $this->readingMode = $mode;
    }
    
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
}

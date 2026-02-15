<?php

declare(strict_types=1);

namespace TombolaNapoletana\Services;

/**
 * Service for managing Smorfia Napoletana
 * Provides cached access to smorfia meanings
 */
class SmorfiaService
{
    private static ?array $smorfia = null;
    
    /**
     * Get smorfia meaning for a number
     */
    public static function getSmorfia(int $number): string
    {
        self::loadSmorfia();
        
        return self::$smorfia[$number] ?? "Sconosciuto";
    }
    
    /**
     * Get all smorfia meanings
     */
    public static function getAllSmorfia(): array
    {
        self::loadSmorfia();
        
        return self::$smorfia;
    }
    
    /**
     * Check if number has smorfia meaning
     */
    public static function hasSmorfia(int $number): bool
    {
        self::loadSmorfia();
        
        return isset(self::$smorfia[$number]);
    }
    
    /**
     * Format smorfia text for speech
     */
    public static function formatSmorfiaText(int $number): string
    {
        $smorfia = self::getSmorfia($number);
        
        return "Nella smorfia napoletana, il numero {$number} è '{$smorfia}'";
    }
    
    /**
     * Get smorfia for multiple numbers
     */
    public static function getMultipleSmorfia(array $numbers): array
    {
        $result = [];
        
        foreach ($numbers as $number) {
            $result[$number] = self::getSmorfia($number);
        }
        
        return $result;
    }
    
    /**
     * Load smorfia data from file
     */
    private static function loadSmorfia(): void
    {
        if (self::$smorfia === null) {
            $smorfiaFile = __DIR__ . '/../../data/smorfia.php';
            
            if (file_exists($smorfiaFile)) {
                self::$smorfia = require $smorfiaFile;
            } else {
                // Fallback data if file doesn't exist
                self::$smorfia = [];
            }
        }
    }
    
    /**
     * Search smorfia by text (partial match)
     */
    public static function searchByKeyword(string $keyword): array
    {
        self::loadSmorfia();
        
        $results = [];
        $keyword = strtolower($keyword);
        
        foreach (self::$smorfia as $number => $meaning) {
            if (str_contains(strtolower($meaning), $keyword)) {
                $results[$number] = $meaning;
            }
        }
        
        return $results;
    }
    
    /**
     * Get random smorfia meaning
     */
    public static function getRandomSmorfia(): array
    {
        self::loadSmorfia();
        
        if (empty(self::$smorfia)) {
            return [0 => 'Nessuna smorfia disponibile'];
        }
        
        $number = array_rand(self::$smorfia);
        
        return [$number => self::$smorfia[$number]];
    }
}

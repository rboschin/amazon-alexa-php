<?php

declare(strict_types=1);

namespace TombolaNapoletana\Services;

use TombolaNapoletana\Config\TombolaConfig;
use TombolaNapoletana\Config\LoggerConfig;

/**
 * Performance optimization service
 */
class PerformanceService
{
    private static array $cache = [];
    private static ?float $startTime = null;
    
    /**
     * Start performance timer
     */
    public static function startTimer(): void
    {
        self::$startTime = microtime(true);
    }
    
    /**
     * End timer and log execution time
     */
    public static function endTimer(string $operation): void
    {
        if (self::$startTime === null) {
            return;
        }
        
        $executionTime = microtime(true) - self::$startTime;
        
        LoggerConfig::debug("Performance: {$operation}", [
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ]);
        
        self::$startTime = null;
    }
    
    /**
     * Cache value with TTL
     */
    public static function cache(string $key, mixed $value, int $ttl = 300): void
    {
        self::$cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
    }
    
    /**
     * Get cached value
     */
    public static function getCached(string $key): mixed
    {
        if (!isset(self::$cache[$key])) {
            return null;
        }
        
        $item = self::$cache[$key];
        
        if ($item['expires'] < time()) {
            unset(self::$cache[$key]);
            return null;
        }
        
        return $item['value'];
    }
    
    /**
     * Clear cache
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
    
    /**
     * Clean expired cache entries
     */
    public static function cleanExpiredCache(): void
    {
        $now = time();
        
        foreach (self::$cache as $key => $item) {
            if ($item['expires'] < $now) {
                unset(self::$cache[$key]);
            }
        }
    }
    
    /**
     * Get cache statistics
     */
    public static function getCacheStats(): array
    {
        $total = count(self::$cache);
        $expired = 0;
        $now = time();
        
        foreach (self::$cache as $item) {
            if ($item['expires'] < $now) {
                $expired++;
            }
        }
        
        return [
            'total_entries' => $total,
            'expired_entries' => $expired,
            'valid_entries' => $total - $expired,
            'memory_usage' => memory_get_usage(true)
        ];
    }
    
    /**
     * Optimize database queries
     */
    public static function optimizeDatabase(): void
    {
        $db = DatabaseService::getInstance();
        
        try {
            // Clean up old sessions (older than 7 days)
            $db->execute(
                'DELETE FROM sessions WHERE updated_at < datetime("now", "-7 days")'
            );
            
            // Vacuum database
            $db->execute('VACUUM');
            
            LoggerConfig::info('Database optimization completed');
            
        } catch (\Exception $e) {
            LoggerConfig::error('Database optimization failed', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get performance metrics
     */
    public static function getMetrics(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'cache_stats' => self::getCacheStats(),
            'database_size' => self::getDatabaseSize()
        ];
    }
    
    /**
     * Get database file size
     */
    private static function getDatabaseSize(): string
    {
        $dbPath = TombolaConfig::getDatabasePath();
        
        if (!file_exists($dbPath)) {
            return '0 bytes';
        }
        
        $size = filesize($dbPath);
        
        return self::formatBytes($size);
    }
    
    /**
     * Format bytes to human readable format
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}

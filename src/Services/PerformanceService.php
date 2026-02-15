<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Services;

use Rboschin\AmazonAlexa\Config\LoggerConfig;

/**
 * Performance monitoring service for Alexa PHP Framework
 */
class PerformanceService
{
    private static array $timers = [];
    private static array $counters = [];
    private static array $gauges = [];
    
    /**
     * Start a performance timer
     */
    public static function startTimer(string $name): void
    {
        if (!LoggerConfig::isPerformanceMonitoringEnabled()) {
            return;
        }
        
        self::$timers[$name] = [
            'start' => microtime(true),
            'start_memory' => memory_get_usage(true)
        ];
    }
    
    /**
     * End a timer and log the duration
     */
    public static function endTimer(string $name, array $context = []): float
    {
        if (!LoggerConfig::isPerformanceMonitoringEnabled()) {
            return 0.0;
        }
        
        if (!isset(self::$timers[$name])) {
            return 0.0;
        }
        
        $timer = self::$timers[$name];
        $duration = microtime(true) - $timer['start'];
        $memoryDelta = memory_get_usage(true) - $timer['start_memory'];
        
        $metrics = array_merge([
            'operation' => $name,
            'duration_ms' => round($duration * 1000, 2),
            'memory_delta' => $memoryDelta,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ], $context);
        
        LoggerConfig::performance($name, $metrics);
        
        unset(self::$timers[$name]);
        
        return $duration;
    }
    
    /**
     * Increment a counter
     */
    public static function incrementCounter(string $name, int $value = 1): void
    {
        if (!LoggerConfig::isPerformanceMonitoringEnabled()) {
            return;
        }
        
        if (!isset(self::$counters[$name])) {
            self::$counters[$name] = 0;
        }
        
        self::$counters[$name] += $value;
        
        LoggerConfig::performance("Counter incremented", [
            'counter' => $name,
            'value' => self::$counters[$name],
            'increment' => $value
        ]);
    }
    
    /**
     * Set a gauge value
     */
    public static function setGauge(string $name, float $value): void
    {
        if (!LoggerConfig::isPerformanceMonitoringEnabled()) {
            return;
        }
        
        self::$gauges[$name] = $value;
        
        LoggerConfig::performance("Gauge set", [
            'gauge' => $name,
            'value' => $value
        ]);
    }
    
    /**
     * Measure execution time of a callable
     */
    public static function measure(string $name, callable $callable, array $context = []): mixed
    {
        if (!LoggerConfig::isPerformanceMonitoringEnabled()) {
            return $callable();
        }
        
        self::startTimer($name);
        
        try {
            $result = $callable();
            
            self::endTimer($name, array_merge($context, [
                'success' => true
            ]));
            
            return $result;
        } catch (\Throwable $e) {
            self::endTimer($name, array_merge($context, [
                'success' => false,
                'error' => $e->getMessage()
            ]));
            
            throw $e;
        }
    }
    
    /**
     * Log memory usage
     */
    public static function logMemoryUsage(string $context = ''): void
    {
        if (!LoggerConfig::isPerformanceMonitoringEnabled()) {
            return;
        }
        
        $metrics = [
            'current_memory' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit')
        ];
        
        if ($context) {
            $metrics['context'] = $context;
        }
        
        LoggerConfig::performance("Memory usage", $metrics);
    }
    
    /**
     * Log database query performance
     */
    public static function logDatabaseQuery(string $query, float $duration, int $affectedRows = 0): void
    {
        if (!LoggerConfig::isPerformanceMonitoringEnabled()) {
            return;
        }
        
        LoggerConfig::databasePerformance($query, $duration, $affectedRows);
    }
    
    /**
     * Log cache operation performance
     */
    public static function logCacheOperation(string $operation, bool $hit, ?float $duration = null): void
    {
        if (!LoggerConfig::isPerformanceMonitoringEnabled()) {
            return;
        }
        
        LoggerConfig::cachePerformance($operation, $hit, $duration);
    }
    
    /**
     * Get performance summary
     */
    public static function getSummary(): array
    {
        return [
            'counters' => self::$counters,
            'gauges' => self::$gauges,
            'active_timers' => array_keys(self::$timers),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit')
        ];
    }
    
    /**
     * Reset all metrics
     */
    public static function reset(): void
    {
        self::$timers = [];
        self::$counters = [];
        self::$gauges = [];
    }
    
    /**
     * Check if performance monitoring is enabled
     */
    public static function isEnabled(): bool
    {
        return LoggerConfig::isPerformanceMonitoringEnabled();
    }
    
    /**
     * Get current timestamp with microseconds
     */
    public static function getTimestamp(): string
    {
        return date('Y-m-d H:i:s.v');
    }
    
    /**
     * Format bytes to human readable format
     */
    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
    
    /**
     * Get memory usage statistics
     */
    public static function getMemoryStats(): array
    {
        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = self::parseMemoryLimit(ini_get('memory_limit'));
        
        return [
            'current' => $current,
            'current_formatted' => self::formatBytes($current),
            'peak' => $peak,
            'peak_formatted' => self::formatBytes($peak),
            'limit' => $limit,
            'limit_formatted' => self::formatBytes($limit),
            'usage_percentage' => $limit > 0 ? round(($current / $limit) * 100, 2) : 0,
            'peak_percentage' => $limit > 0 ? round(($peak / $limit) * 100, 2) : 0
        ];
    }
    
    /**
     * Parse memory limit string to bytes
     */
    private static function parseMemoryLimit(string $limit): int
    {
        $limit = strtolower($limit);
        $multiplier = 1;
        
        if (str_ends_with($limit, 'g')) {
            $multiplier = 1024 * 1024 * 1024;
            $limit = substr($limit, 0, -1);
        } elseif (str_ends_with($limit, 'm')) {
            $multiplier = 1024 * 1024;
            $limit = substr($limit, 0, -1);
        } elseif (str_ends_with($limit, 'k')) {
            $multiplier = 1024;
            $limit = substr($limit, 0, -1);
        }
        
        return (int) $limit * $multiplier;
    }
}

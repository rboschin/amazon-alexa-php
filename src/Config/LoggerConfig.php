<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Config;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

/**
 * Logger configuration for Alexa PHP Framework
 */
class LoggerConfig
{
    private static ?Logger $logger = null;
    private static ?Logger $performanceLogger = null;
    
    /**
     * Get main logger instance
     */
    public static function getLogger(): Logger
    {
        if (self::$logger === null) {
            self::initializeLogger();
        }
        
        return self::$logger;
    }
    
    /**
     * Get performance logger instance
     */
    public static function getPerformanceLogger(): Logger
    {
        if (self::$performanceLogger === null) {
            self::initializePerformanceLogger();
        }
        
        return self::$performanceLogger;
    }
    
    /**
     * Check if logging is enabled
     */
    public static function isLoggingEnabled(): bool
    {
        $enabled = $_ENV['LOGGING_ENABLED'] ?? 'true';
        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Check if performance monitoring is enabled
     */
    public static function isPerformanceMonitoringEnabled(): bool
    {
        $enabled = $_ENV['PERFORMANCE_MONITORING_ENABLED'] ?? 'true';
        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Get log level from environment
     */
    private static function getLogLevel(): int
    {
        $level = strtoupper($_ENV['LOG_LEVEL'] ?? 'INFO');
        
        return match($level) {
            'DEBUG' => Logger::DEBUG,
            'INFO' => Logger::INFO,
            'WARNING' => Logger::WARNING,
            'ERROR' => Logger::ERROR,
            'CRITICAL' => Logger::CRITICAL,
            default => Logger::INFO
        };
    }
    
    /**
     * Get log file path
     */
    private static function getLogPath(): string
    {
        return $_ENV['LOG_PATH'] ?? 'logs/alexa.log';
    }
    
    /**
     * Get performance log file path
     */
    private static function getPerformanceLogPath(): string
    {
        return $_ENV['PERFORMANCE_LOG_PATH'] ?? 'logs/performance.log';
    }
    
    /**
     * Initialize main logger
     */
    private static function initializeLogger(): void
    {
        if (!self::isLoggingEnabled()) {
            self::$logger = new Logger('disabled');
            return;
        }
        
        $logger = new Logger('alexa');
        $logPath = self::getLogPath();
        $logDir = dirname($logPath);
        
        // Create log directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // File handler with rotation
        $fileHandler = new RotatingFileHandler($logPath, 30, self::getLogLevel());
        
        // Custom formatter
        $formatter = new LineFormatter(
            '[%datetime%] %level_name%: %message% %context% %extra%' . PHP_EOL,
            'Y-m-d H:i:s',
            true,
            true
        );
        
        $fileHandler->setFormatter($formatter);
        $logger->pushHandler($fileHandler);
        
        // Console handler for debug mode
        if (self::isDebugMode()) {
            $consoleHandler = new StreamHandler('php://stdout', Logger::DEBUG);
            $consoleHandler->setFormatter($formatter);
            $logger->pushHandler($consoleHandler);
        }
        
        self::$logger = $logger;
    }
    
    /**
     * Initialize performance logger
     */
    private static function initializePerformanceLogger(): void
    {
        if (!self::isPerformanceMonitoringEnabled()) {
            self::$performanceLogger = new Logger('disabled');
            return;
        }
        
        $logger = new Logger('performance');
        $logPath = self::getPerformanceLogPath();
        $logDir = dirname($logPath);
        
        // Create log directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // File handler with rotation
        $fileHandler = new RotatingFileHandler($logPath, 30, Logger::INFO);
        
        // Performance-specific formatter
        $formatter = new LineFormatter(
            '[%datetime%] %level_name%: %message% %context% %extra%' . PHP_EOL,
            'Y-m-d H:i:s.v', // Include milliseconds
            true,
            true
        );
        
        $fileHandler->setFormatter($formatter);
        $logger->pushHandler($fileHandler);
        
        self::$performanceLogger = $logger;
    }
    
    /**
     * Check if debug mode is enabled
     */
    private static function isDebugMode(): bool
    {
        $debug = $_ENV['DEBUG'] ?? 'false';
        return filter_var($debug, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        if (self::isLoggingEnabled()) {
            self::getLogger()->debug($message, $context);
        }
    }
    
    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        if (self::isLoggingEnabled()) {
            self::getLogger()->info($message, $context);
        }
    }
    
    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        if (self::isLoggingEnabled()) {
            self::getLogger()->warning($message, $context);
        }
    }
    
    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        if (self::isLoggingEnabled()) {
            self::getLogger()->error($message, $context);
        }
    }
    
    /**
     * Log critical message
     */
    public static function critical(string $message, array $context = []): void
    {
        if (self::isLoggingEnabled()) {
            self::getLogger()->critical($message, $context);
        }
    }
    
    /**
     * Log performance metric
     */
    public static function performance(string $operation, array $metrics): void
    {
        if (self::isPerformanceMonitoringEnabled()) {
            self::getPerformanceLogger()->info($operation, $metrics);
        }
    }
    
    /**
     * Log request/response performance
     */
    public static function requestPerformance(string $intent, float $duration, array $extra = []): void
    {
        if (self::isPerformanceMonitoringEnabled()) {
            $context = array_merge([
                'intent' => $intent,
                'duration_ms' => round($duration * 1000, 2),
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ], $extra);
            
            self::getPerformanceLogger()->info("Request processed", $context);
        }
    }
    
    /**
     * Log database performance
     */
    public static function databasePerformance(string $query, float $duration, int $affectedRows = 0): void
    {
        if (self::isPerformanceMonitoringEnabled()) {
            self::getPerformanceLogger()->info("Database query", [
                'query_type' => $query,
                'duration_ms' => round($duration * 1000, 2),
                'affected_rows' => $affectedRows
            ]);
        }
    }
    
    /**
     * Log cache performance
     */
    public static function cachePerformance(string $operation, bool $hit, ?float $duration = null): void
    {
        if (self::isPerformanceMonitoringEnabled()) {
            $context = [
                'operation' => $operation,
                'cache_hit' => $hit,
            ];
            
            if ($duration !== null) {
                $context['duration_ms'] = round($duration * 1000, 2);
            }
            
            self::getPerformanceLogger()->info("Cache operation", $context);
        }
    }
}

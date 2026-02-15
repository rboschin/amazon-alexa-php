<?php

declare(strict_types=1);

namespace TombolaNapoletana\Config;

use TombolaNapoletana\Config\TombolaConfig;

/**
 * Logger configuration for Tombola Napoletana
 */
class LoggerConfig
{
    private static ?\Monolog\Logger $logger = null;
    
    /**
     * Get logger instance
     */
    public static function getLogger(): \Monolog\Logger
    {
        if (self::$logger === null) {
            self::initializeLogger();
        }
        
        return self::$logger;
    }
    
    /**
     * Initialize logger with handlers
     */
    private static function initializeLogger(): void
    {
        $logger = new \Monolog\Logger('tombola');
        
        // File handler
        $logFile = TombolaConfig::getLogPath();
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $fileHandler = new \Monolog\Handler\StreamHandler($logFile, self::getLogLevel());
        $logger->pushHandler($fileHandler);
        
        // Console handler for debug mode
        if (TombolaConfig::isDebug()) {
            $consoleHandler = new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG);
            $logger->pushHandler($consoleHandler);
        }
        
        // Custom formatter
        $formatter = new \Monolog\Formatter\LineFormatter(
            '[%datetime%] %level_name%: %message% %context% %extra%' . PHP_EOL,
            'Y-m-d H:i:s'
        );
        
        $fileHandler->setFormatter($formatter);
        
        if (TombolaConfig::isDebug()) {
            $consoleHandler->setFormatter($formatter);
        }
        
        self::$logger = $logger;
    }
    
    /**
     * Get log level from config
     */
    private static function getLogLevel(): int
    {
        $level = strtoupper(TombolaConfig::getLogLevel());
        
        return match($level) {
            'DEBUG' => \Monolog\Logger::DEBUG,
            'INFO' => \Monolog\Logger::INFO,
            'WARNING' => \Monolog\Logger::WARNING,
            'ERROR' => \Monolog\Logger::ERROR,
            'CRITICAL' => \Monolog\Logger::CRITICAL,
            default => \Monolog\Logger::INFO
        };
    }
    
    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }
    
    /**
     * Log critical message
     */
    public static function critical(string $message, array $context = []): void
    {
        self::getLogger()->critical($message, $context);
    }
}

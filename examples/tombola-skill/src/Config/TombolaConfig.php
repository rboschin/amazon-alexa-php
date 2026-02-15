<?php

declare(strict_types=1);

namespace TombolaNapoletana\Config;

use function getenv;
use function file_exists;
use function parse_ini_file;

/**
 * Configuration constants for Tombola Napoletana
 */
class TombolaConfig
{
    // Game constants
    public const MIN_NUMBER = 1;
    public const MAX_NUMBER = 90;
    public const AUTO_MODE_COUNT = 5;
    
    // Reading modes
    public const READING_MODE_NORMAL = 'normal';
    public const READING_MODE_SLOW = 'slow';
    public const READING_MODE_AUTO = 'auto';
    
    // Winning combinations
    public const WINNING_COMBINATIONS = [
        'ambo' => 2,
        'terna' => 3,
        'quaterna' => 4,
        'cinquina' => 5,
        'tombola' => 15
    ];
    
    private static ?array $env = null;
    
    /**
     * Load environment variables from .env file
     */
    private static function loadEnv(): void
    {
        if (self::$env === null) {
            $envFile = __DIR__ . '/../../config/.env';
            if (file_exists($envFile)) {
                self::$env = parse_ini_file($envFile);
            } else {
                self::$env = [];
            }
        }
    }
    
    /**
     * Get environment variable
     */
    private static function getEnv(string $key, mixed $default = null): mixed
    {
        self::loadEnv();
        return self::$env[$key] ?? getenv($key) ?? $default;
    }
    
    /**
     * Get database path
     */
    public static function getDatabasePath(): string
    {
        return self::getEnv('DB_PATH', './data/tombola.db');
    }
    
    /**
     * Get log path
     */
    public static function getLogPath(): string
    {
        return self::getEnv('LOG_PATH', './logs/tombola.log');
    }
    
    /**
     * Get log level
     */
    public static function getLogLevel(): string
    {
        return self::getEnv('LOG_LEVEL', 'INFO');
    }
    
    /**
     * Is debug mode enabled?
     */
    public static function isDebug(): bool
    {
        return self::getEnv('DEBUG', 'false') === 'true';
    }
    
    /**
     * Get Alexa application ID
     */
    public static function getAlexaAppId(): string
    {
        return self::getEnv('ALEXA_APP_ID', '');
    }
    
    /**
     * Get skill name
     */
    public static function getSkillName(): string
    {
        return self::getEnv('SKILL_NAME', 'Tombola Napoletana');
    }
    
    /**
     * Get invocation name
     */
    public static function getInvocationName(): string
    {
        return self::getEnv('INVOCATION_NAME', 'tombola napoletana');
    }
    
    /**
     * Get auto mode count
     */
    public static function getAutoModeCount(): int
    {
        return (int) self::getEnv('AUTO_MODE_COUNT', (string) self::AUTO_MODE_COUNT);
    }
}

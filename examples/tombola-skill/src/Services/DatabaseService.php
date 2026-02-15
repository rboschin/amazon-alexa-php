<?php

declare(strict_types=1);

namespace TombolaNapoletana\Services;

use PDO;
use PDOException;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Database service for Tombola Napoletana
 * Provides singleton pattern for database connections
 */
class DatabaseService
{
    private static ?self $instance = null;
    private ?PDO $pdo = null;
    
    private function __construct()
    {
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Get database connection
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->initializeConnection();
        }
        
        return $this->pdo;
    }
    
    /**
     * Initialize database connection and create tables if needed
     */
    private function initializeConnection(): void
    {
        $dbPath = TombolaConfig::getDatabasePath();
        
        // Ensure data directory exists
        $dataDir = dirname($dbPath);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        try {
            $this->pdo = new PDO("sqlite:{$dbPath}");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->createTables();
            
        } catch (PDOException $e) {
            throw new \RuntimeException("Failed to connect to database: {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * Create necessary database tables
     */
    private function createTables(): void
    {
        $pdo = $this->getConnection();
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id TEXT NOT NULL UNIQUE,
                extracted_numbers TEXT NOT NULL DEFAULT '[]',
                last_number INTEGER DEFAULT NULL,
                last_smorfia TEXT DEFAULT NULL,
                reading_mode TEXT NOT NULL DEFAULT 'normal',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS game_numbers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                number INTEGER NOT NULL,
                smorfia TEXT NOT NULL,
                extracted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
                UNIQUE(session_id, number)
            )
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_numbers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                numbers TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
            )
        ");
        
        // Create indexes for performance
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_game_numbers_session_id ON game_numbers(session_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_user_numbers_session_id ON user_numbers(session_id)");
    }
    
    /**
     * Execute prepared statement with error handling
     */
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \RuntimeException("Database query failed: {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): void
    {
        $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->getConnection()->rollBack();
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }
}

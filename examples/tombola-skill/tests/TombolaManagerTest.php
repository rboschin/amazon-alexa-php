<?php

declare(strict_types=1);

namespace TombolaNapoletana\Tests;

use PHPUnit\Framework\TestCase;
use TombolaNapoletana\Services\TombolaManager;
use TombolaNapoletana\Services\DatabaseService;
use TombolaNapoletana\Config\TombolaConfig;

/**
 * Unit tests for TombolaManager
 */
class TombolaManagerTest extends TestCase
{
    private TombolaManager $tombola;
    private string $testUserId = 'test-user-123';
    
    protected function setUp(): void
    {
        // Use in-memory database for testing
        putenv('DB_PATH=:memory:');
        
        $this->tombola = new TombolaManager($this->testUserId);
    }
    
    protected function tearDown(): void
    {
        // Clean up test database
        $db = DatabaseService::getInstance();
        $db->execute('DELETE FROM sessions WHERE user_id = ?', [$this->testUserId]);
    }
    
    public function testGenerateRandomNumber(): void
    {
        $number = $this->tombola->generateRandomNumber();
        
        $this->assertNotNull($number);
        $this->assertGreaterThanOrEqual(1, $number);
        $this->assertLessThanOrEqual(90, $number);
    }
    
    public function testExtractNumber(): void
    {
        $number = $this->tombola->generateRandomNumber();
        $this->assertNotNull($number);
        
        $this->tombola->extractNumber($number);
        
        $this->assertTrue($this->tombola->isNumberExtracted($number));
        $this->assertEquals(1, $this->tombola->getExtractedCount());
        $this->assertEquals($number, $this->tombola->getLastNumber());
    }
    
    public function testCannotExtractSameNumberTwice(): void
    {
        $number = $this->tombola->generateRandomNumber();
        $this->assertNotNull($number);
        
        $this->tombola->extractNumber($number);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->tombola->extractNumber($number);
    }
    
    public function testCheckNumberNotExtracted(): void
    {
        $this->assertFalse($this->tombola->isNumberExtracted(50));
    }
    
    public function testGetAvailableNumbers(): void
    {
        $number = $this->tombola->generateRandomNumber();
        $this->assertNotNull($number);
        
        $this->tombola->extractNumber($number);
        
        $availableNumbers = $this->tombola->getAvailableNumbers();
        
        $this->assertNotContains($number, $availableNumbers);
        $this->assertCount(89, $availableNumbers);
    }
    
    public function testGameNotCompleteInitially(): void
    {
        $this->assertFalse($this->tombola->isComplete());
    }
    
    public function testReadingMode(): void
    {
        $this->assertEquals(TombolaConfig::READING_MODE_NORMAL, $this->tombola->getReadingMode());
        
        $this->tombola->setReadingMode(TombolaConfig::READING_MODE_SLOW);
        $this->assertEquals(TombolaConfig::READING_MODE_SLOW, $this->tombola->getReadingMode());
    }
    
    public function testGetGameStatus(): void
    {
        $status = $this->tombola->getGameStatus();
        
        $this->assertArrayHasKey('extracted_count', $status);
        $this->assertArrayHasKey('last_number', $status);
        $this->assertArrayHasKey('last_smorfia', $status);
        $this->assertArrayHasKey('reading_mode', $status);
        $this->assertArrayHasKey('is_complete', $status);
        $this->assertArrayHasKey('available_count', $status);
        
        $this->assertEquals(0, $status['extracted_count']);
        $this->assertEquals(90, $status['available_count']);
        $this->assertFalse($status['is_complete']);
    }
    
    public function testResetGame(): void
    {
        // Extract some numbers first
        $number1 = $this->tombola->generateRandomNumber();
        $number2 = $this->tombola->generateRandomNumber();
        
        $this->tombola->extractNumber($number1);
        $this->tombola->extractNumber($number2);
        
        $this->assertEquals(2, $this->tombola->getExtractedCount());
        
        // Reset game
        $this->tombola->resetGame();
        
        $this->assertEquals(0, $this->tombola->getExtractedCount());
        $this->assertNull($this->tombola->getLastNumber());
        $this->assertFalse($this->tombola->isNumberExtracted($number1));
        $this->assertFalse($this->tombola->isNumberExtracted($number2));
    }
    
    public function testAutoModeGeneration(): void
    {
        $numbers = $this->tombola->generateAutoModeNumbers(3);
        
        $this->assertCount(3, $numbers);
        $this->assertEquals(3, $this->tombola->getExtractedCount());
        
        foreach ($numbers as $data) {
            $this->assertArrayHasKey('number', $data);
            $this->assertArrayHasKey('smorfia', $data);
            $this->assertGreaterThanOrEqual(1, $data['number']);
            $this->assertLessThanOrEqual(90, $data['number']);
            $this->assertNotEmpty($data['smorfia']);
        }
    }
}

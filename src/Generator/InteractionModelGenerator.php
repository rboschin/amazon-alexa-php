<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Generator;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest;
use Rboschin\AmazonAlexa\Intent\Intent;
use Rboschin\AmazonAlexa\Intent\Slot;
use ReflectionClass;
use ReflectionMethod;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Interaction Model Generator
 * 
 * Automatically generates Alexa interaction model JSON from handler classes
 * by analyzing intent names, slots, and patterns.
 */
class InteractionModelGenerator
{
    private array $intents = [];
    private array $handlers = [];
    private string $skillName;
    private string $locale = 'en-US';
    private string $invocationName;
    private UtteranceExtractor $utteranceExtractor;

    public function __construct(string $skillName, string $invocationName, string $locale = 'en-US')
    {
        $this->skillName = $skillName;
        $this->invocationName = $invocationName;
        $this->locale = $locale;
        $this->utteranceExtractor = new UtteranceExtractor();
    }

    /**
     * Add a handler to analyze
     */
    public function addHandler(AbstractRequestHandler $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    /**
     * Load handlers from a directory
     */
    public function loadHandlersFromDirectory(string $directory, string $namespace): self
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("Directory not found: {$directory}");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = $namespace . '\\' . $file->getBasename('.php');
                
                // Try to include the file first
                $filePath = $file->getPathname();
                require_once $filePath;
                
                if (class_exists($className)) {
                    try {
                        $reflection = new ReflectionClass($className);
                        if ($reflection->isSubclassOf(AbstractRequestHandler::class) && !$reflection->isAbstract()) {
                            $this->handlers[] = $reflection->newInstance();
                        }
                    } catch (\ReflectionException $e) {
                        // Skip invalid classes
                        continue;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Analyze handlers and extract intents
     */
    public function analyze(): self
    {
        foreach ($this->handlers as $handler) {
            $this->analyzeHandler($handler);
        }

        // Extract utterances from docblocks
        $handlerClasses = array_map('get_class', $this->handlers);
        $docblockUtterances = $this->utteranceExtractor->extractFromClasses($handlerClasses);
        
        // Merge utterances with existing intents
        foreach ($docblockUtterances as $intentName => $utterances) {
            if (isset($this->intents[$intentName])) {
                $this->intents[$intentName]['samples'] = array_merge(
                    $this->intents[$intentName]['samples'],
                    $utterances
                );
            }
        }

        return $this;
    }

    /**
     * Analyze a single handler
     */
    private function analyzeHandler(AbstractRequestHandler $handler): void
    {
        $reflection = new ReflectionClass($handler);
        $handlerClass = $reflection->getName();

        // Check for LaunchRequest handler (special case)
        if ($reflection->hasMethod('supportsRequest')) {
            $method = $reflection->getMethod('supportsRequest');
            $source = file_get_contents($method->getFileName());
            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();
            
            $lines = explode("\n", $source);
            $methodLines = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);
            $methodCode = implode("\n", $methodLines);

            // Check if it handles LaunchRequest
            if (str_contains($methodCode, 'LaunchRequest')) {
                $this->intents['LaunchRequest'] = [
                    'name' => 'LaunchRequest',
                    'samples' => [],
                    'slots' => [],
                    'handler' => $handlerClass,
                ];
            }
        }

        // Check supportsRequest method to find intent patterns
        if ($reflection->hasMethod('supportsRequest')) {
            $method = $reflection->getMethod('supportsRequest');
            $source = file_get_contents($method->getFileName());
            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();
            
            $lines = explode("\n", $source);
            $methodLines = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);
            $methodCode = implode("\n", $methodLines);

            // Extract intent names from supportsRequest method
            $this->extractIntentsFromCode($methodCode, $handlerClass);
        }

        // Check handleRequest method for slot usage
        if ($reflection->hasMethod('handleRequest')) {
            $method = $reflection->getMethod('handleRequest');
            $source = file_get_contents($method->getFileName());
            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();
            
            $lines = explode("\n", $source);
            $methodLines = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);
            $methodCode = implode("\n", $methodLines);

            // Extract slot information from handleRequest method
            $this->extractSlotsFromCode($methodCode);
        }
    }

    /**
     * Extract intent names from supportsRequest method code
     */
    private function extractIntentsFromCode(string $code, string $handlerClass): void
    {
        // Common patterns for intent checking
        $patterns = [
            '/intent->name === [\'"]([^\'"]+)[\'"]/',
            '/intent->name === [\'"]([^\'"]+)[\'"]/',
            '/AMAZON\.([A-Za-z]+)Intent/',
            '/[\'"]([A-Za-z][A-Za-z0-9]*Intent)[\'"]/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $code, $matches)) {
                foreach ($matches[1] as $intentName) {
                    if (!isset($this->intents[$intentName])) {
                        $this->intents[$intentName] = [
                            'name' => $intentName,
                            'samples' => [],
                            'slots' => [],
                            'handler' => $handlerClass,
                        ];
                    }
                }
            }
        }
    }

    /**
     * Extract slot information from handleRequest method code
     */
    private function extractSlotsFromCode(string $code): void
    {
        // Patterns for slot access
        $patterns = [
            '/slots\[([\'"]([^\'"]+)[\'"]\]/',
            '/intent->slots\[([\'"]([^\'"]+)[\'"]\]/',
            '/slot->value/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $code, $matches)) {
                foreach ($matches[1] as $slotName) {
                    // Try to infer slot type from context
                    $slotType = $this->inferSlotType($code, $slotName);
                    
                    // Add slot to all intents that might use it
                    foreach ($this->intents as $intentName => &$intent) {
                        if (!isset($intent['slots'][$slotName])) {
                            $intent['slots'][$slotName] = [
                                'name' => $slotName,
                                'type' => $slotType,
                                'samples' => [],
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
     * Infer slot type from context
     */
    private function inferSlotType(string $code, string $slotName): string
    {
        // Common patterns for slot types
        $typePatterns = [
            '/number|count|quantity|age/i' => 'AMAZON.NUMBER',
            '/date|time|when/i' => 'AMAZON.DATE',
            '/city|country|location/i' => 'AMAZON.City',
            '/name|person|who/i' => 'AMAZON.Person',
            '/yes|no|confirm/i' => 'AMAZON.Boolean',
            '/list|options|choice/i' => 'AMAZON.LIST',
            '/duration|how.*long/i' => 'AMAZON.DURATION',
        ];

        foreach ($typePatterns as $pattern => $type) {
            if (preg_match($pattern, $slotName) || preg_match($pattern, $code)) {
                return $type;
            }
        }

        return 'AMAZON.LITERAL'; // Default type
    }

    /**
     * Add sample utterances for an intent
     */
    public function addSamples(string $intentName, array $samples): self
    {
        if (isset($this->intents[$intentName])) {
            $this->intents[$intentName]['samples'] = array_merge(
                $this->intents[$intentName]['samples'],
                $samples
            );
        }

        return $this;
    }

    /**
     * Generate the interaction model JSON
     */
    public function generate(): string
    {
        $model = [
            'interactionModel' => [
                'languageModel' => [
                    'invocationName' => $this->invocationName,
                    'intents' => $this->buildIntentsArray(),
                ],
            ],
        ];

        return json_encode($model, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Build intents array for JSON output
     */
    private function buildIntentsArray(): array
    {
        $intentsArray = [];

        foreach ($this->intents as $intentName => $intentData) {
            $intent = [
                'name' => $intentName,
                'samples' => $this->generateDefaultSamples($intentName, $intentData['samples']),
            ];

            if (!empty($intentData['slots'])) {
                $intent['slots'] = $this->buildSlotsArray($intentData['slots']);
            }

            $intentsArray[] = $intent;
        }

        return $intentsArray;
    }

    /**
     * Build slots array for JSON output
     */
    private function buildSlotsArray(array $slots): array
    {
        $slotsArray = [];

        foreach ($slots as $slotName => $slotData) {
            $slot = [
                'name' => $slotName,
                'type' => $slotData['type'],
            ];

            if (!empty($slotData['samples'])) {
                $slot['samples'] = $slotData['samples'];
            }

            $slotsArray[] = $slot;
        }

        return $slotsArray;
    }

    /**
     * Generate default sample utterances
     */
    private function generateDefaultSamples(string $intentName, array $existingSamples): array
    {
        if (!empty($existingSamples)) {
            return $existingSamples;
        }

        // First try to get default utterances from UtteranceExtractor
        $defaultUtterances = $this->utteranceExtractor->generateDefaultUtterances($intentName);
        if (!empty($defaultUtterances)) {
            return array_map(fn($sample) => str_replace('{skill}', $this->invocationName, $sample), $defaultUtterances);
        }

        // Generate suggestions based on intent name
        $suggestions = $this->utteranceExtractor->suggestUtterances($intentName);
        if (!empty($suggestions)) {
            return $suggestions;
        }

        // Fallback to basic patterns
        switch ($intentName) {
            case 'LaunchRequest':
                return [
                    'open ' . $this->invocationName,
                    'launch ' . $this->invocationName,
                    'start ' . $this->invocationName,
                ];

            default:
                // Generate samples based on intent name
                $cleanName = str_replace(['Intent', 'AMAZON.'], '', $intentName);
                return [
                    strtolower($cleanName),
                    'to ' . strtolower($cleanName),
                    'I want to ' . strtolower($cleanName),
                ];
        }
    }

    /**
     * Save interaction model to file
     */
    public function saveToFile(string $filename): void
    {
        $json = $this->generate();
        file_put_contents($filename, $json);
    }

    /**
     * Get generated intents information
     */
    public function getIntents(): array
    {
        return $this->intents;
    }

    /**
     * Get handlers information
     */
    public function getHandlers(): array
    {
        return array_map('get_class', $this->handlers);
    }
}

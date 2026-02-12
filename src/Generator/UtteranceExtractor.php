<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Generator;

use ReflectionClass;
use ReflectionMethod;

/**
 * Utterance Extractor
 * 
 * Extracts sample utterances from PHPDoc blocks using @utterances tag
 */
class UtteranceExtractor
{
    /**
     * Extract utterances from a handler class
     */
    public function extractFromClass(string $className): array
    {
        $utterances = [];
        
        if (!class_exists($className)) {
            return $utterances;
        }

        $reflection = new ReflectionClass($className);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            // Look for methods that handle intents
            if ($this->isIntentHandlerMethod($method)) {
                $intentName = $this->extractIntentName($method);
                $methodUtterances = $this->extractFromMethod($method);
                
                if (!empty($methodUtterances)) {
                    $utterances[$intentName] = $methodUtterances;
                }
            }
        }

        return $utterances;
    }

    /**
     * Check if method is an intent handler
     */
    private function isIntentHandlerMethod(ReflectionMethod $method): bool
    {
        $methodName = $method->getName();
        
        // Pattern: handleXxxIntent or supportsXxxIntent
        return (
            str_starts_with($methodName, 'handle') && 
            str_ends_with($methodName, 'Intent')
        ) || (
            str_starts_with($methodName, 'supports') && 
            str_ends_with($methodName, 'Intent')
        );
    }

    /**
     * Extract intent name from method name
     */
    private function extractIntentName(ReflectionMethod $method): string
    {
        $methodName = $method->getName();
        
        if (str_starts_with($methodName, 'handle')) {
            return substr($methodName, 6); // Remove 'handle'
        } elseif (str_starts_with($methodName, 'supports')) {
            return substr($methodName, 8); // Remove 'supports'
        }
        
        return $methodName;
    }

    /**
     * Extract utterances from method docblock
     */
    private function extractFromMethod(ReflectionMethod $method): array
    {
        $utterances = [];
        $docComment = $method->getDocComment();

        if ($docComment === false) {
            return $utterances;
        }

        // Extract @utterances tags
        if (preg_match_all('/@utterances\s+(.+)/', $docComment, $matches)) {
            foreach ($matches[1] as $match) {
                // Split by comma and clean up
                $lines = explode(',', $match);
                foreach ($lines as $line) {
                    $utterance = trim($line);
                    if (!empty($utterance)) {
                        $utterances[] = $utterance;
                    }
                }
            }
        }

        return $utterances;
    }

    /**
     * Extract utterances from multiple classes
     */
    public function extractFromClasses(array $classNames): array
    {
        $allUtterances = [];

        foreach ($classNames as $className) {
            $classUtterances = $this->extractFromClass($className);
            $allUtterances = array_merge($allUtterances, $classUtterances);
        }

        return $allUtterances;
    }

    /**
     * Generate sample utterances for common intents
     */
    public function generateDefaultUtterances(string $intentName): array
    {
        $defaults = [
            'LaunchRequest' => [
                'open {skill}',
                'launch {skill}',
                'start {skill}',
                'begin {skill}'
            ],
            'AMAZON.HelpIntent' => [
                'help',
                'what can I do',
                'help me',
                'what can you do',
                'how do I use this'
            ],
            'AMAZON.CancelIntent' => [
                'cancel',
                'never mind',
                'stop',
                'forget it'
            ],
            'AMAZON.StopIntent' => [
                'stop',
                'end',
                'quit',
                'goodbye'
            ],
            'AMAZON.YesIntent' => [
                'yes',
                'yeah',
                'sure',
                'okay',
                'that\'s right'
            ],
            'AMAZON.NoIntent' => [
                'no',
                'nope',
                'not really',
                'I don\'t think so'
            ],
            'AMAZON.MoreIntent' => [
                'more',
                'tell me more',
                'give me more',
                'continue'
            ],
            'AMAZON.RepeatIntent' => [
                'repeat',
                'say that again',
                'can you repeat',
                'what did you say'
            ],
            'AMAZON.StartOverIntent' => [
                'start over',
                'restart',
                'begin again',
                'start from the beginning'
            ],
            'AMAZON.NextIntent' => [
                'next',
                'go to the next',
                'continue',
                'move on'
            ],
            'AMAZON.PreviousIntent' => [
                'previous',
                'go back',
                'last one',
                'the previous'
            ]
        ];

        return $defaults[$intentName] ?? [];
    }

    /**
     * Suggest utterances based on intent name
     */
    public function suggestUtterances(string $intentName): array
    {
        $suggestions = [];
        
        // Remove common suffixes
        $cleanName = str_replace(['Intent', 'AMAZON.'], '', $intentName);
        
        // Generate variations based on common patterns
        if (str_contains($cleanName, 'Order')) {
            $item = str_replace('Order', '', $cleanName);
            $suggestions = [
                "order {$item}",
                "I want to order {$item}",
                "get me {$item}",
                "can I have {$item}",
                "I'd like {$item}"
            ];
        } elseif (str_contains($cleanName, 'Get')) {
            $item = str_replace('Get', '', $cleanName);
            $suggestions = [
                "get {$item}",
                "I want {$item}",
                "can I get {$item}",
                "give me {$item}",
                "show me {$item}"
            ];
        } elseif (str_contains($cleanName, 'Find')) {
            $item = str_replace('Find', '', $cleanName);
            $suggestions = [
                "find {$item}",
                "search for {$item}",
                "look for {$item}",
                "where is {$item}",
                "locate {$item}"
            ];
        } elseif (str_contains($cleanName, 'Play')) {
            $item = str_replace('Play', '', $cleanName);
            $suggestions = [
                "play {$item}",
                "I want to play {$item}",
                "can you play {$item}",
                "start playing {$item}",
                "put on {$item}"
            ];
        } elseif (str_contains($cleanName, 'Set')) {
            $item = str_replace('Set', '', $cleanName);
            $suggestions = [
                "set {$item}",
                "change {$item}",
                "update {$item}",
                "modify {$item}",
                "adjust {$item}"
            ];
        } else {
            // Generic suggestions
            $suggestions = [
                strtolower($cleanName),
                "to " . strtolower($cleanName),
                "I want to " . strtolower($cleanName),
                "can you " . strtolower($cleanName),
                "help me " . strtolower($cleanName)
            ];
        }

        return $suggestions;
    }
}

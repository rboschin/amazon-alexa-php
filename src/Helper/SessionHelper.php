<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Helper;

use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\Response;

/**
 * SessionHelper provides convenient methods for managing session attributes
 * 
 * This helper simplifies reading and writing session attributes
 * without having to manually access the request->session->attributes array.
 */
class SessionHelper
{
    /**
     * Get a session attribute value
     *
     * @param mixed $default Default value if attribute doesn't exist
     */
    public static function get(Request $request, string $key, mixed $default = null): mixed
    {
        return $request->session->attributes[$key] ?? $default;
    }

    /**
     * Set a session attribute in the response
     * 
     * Note: This method modifies the response object directly
     */
    public static function set(Response $response, string $key, mixed $value): void
    {
        $response->sessionAttributes[$key] = $value;
    }

    /**
     * Get all session attributes as array
     */
    public static function all(Request $request): array
    {
        return $request->session->attributes ?? [];
    }

    /**
     * Check if a session attribute exists
     */
    public static function has(Request $request, string $key): bool
    {
        return isset($request->session->attributes[$key]);
    }

    /**
     * Remove a session attribute from response
     * 
     * Note: This sets the attribute to null to remove it
     */
    public static function remove(Response $response, string $key): void
    {
        $response->sessionAttributes[$key] = null;
    }

    /**
     * Clear all session attributes
     */
    public static function clear(Response $response): void
    {
        $response->sessionAttributes = [];
    }

    /**
     * Increment a numeric session attribute
     */
    public static function increment(Request $request, Response $response, string $key, int $step = 1): int
    {
        $current = self::get($request, $key, 0);
        $newValue = $current + $step;
        self::set($response, $key, $newValue);
        
        return $newValue;
    }

    /**
     * Decrement a numeric session attribute
     */
    public static function decrement(Request $request, Response $response, string $key, int $step = 1): int
    {
        $current = self::get($request, $key, 0);
        $newValue = max(0, $current - $step);
        self::set($response, $key, $newValue);
        
        return $newValue;
    }

    /**
     * Push value to a session array attribute
     */
    public static function push(Request $request, Response $response, string $key, mixed $value): void
    {
        $array = self::get($request, $key, []);
        $array[] = $value;
        self::set($response, $key, $array);
    }

    /**
     * Get last value from a session array attribute
     */
    public static function last(Request $request, string $key): mixed
    {
        $array = self::get($request, $key, []);
        return end($array) ?: null;
    }

    /**
     * Pop last value from a session array attribute
     */
    public static function pop(Request $request, Response $response, string $key): mixed
    {
        $array = self::get($request, $key, []);
        $value = array_pop($array);
        self::set($response, $key, $array);
        
        return $value;
    }

    /**
     * Get session attribute as integer
     */
    public static function getInt(Request $request, string $key, int $default = 0): int
    {
        return (int) self::get($request, $key, $default);
    }

    /**
     * Get session attribute as string
     */
    public static function getString(Request $request, string $key, string $default = ''): string
    {
        return (string) self::get($request, $key, $default);
    }

    /**
     * Get session attribute as boolean
     */
    public static function getBool(Request $request, string $key, bool $default = false): bool
    {
        return (bool) self::get($request, $key, $default);
    }

    /**
     * Get session attribute as array
     */
    public static function getArray(Request $request, string $key, array $default = []): array
    {
        $value = self::get($request, $key, $default);
        return is_array($value) ? $value : $default;
    }

    /**
     * Merge multiple session attributes at once
     */
    public static function merge(Response $response, array $attributes): void
    {
        $response->sessionAttributes = array_merge($response->sessionAttributes, $attributes);
    }

    /**
     * Get session attribute with dot notation (e.g., 'user.profile.name')
     */
    public static function getNested(Request $request, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = self::all($request);
        
        foreach ($keys as $k) {
            if (!is_array($value) || !isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    /**
     * Set session attribute with dot notation (e.g., 'user.profile.name')
     */
    public static function setNested(Response $response, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $attributes = $response->sessionAttributes;
        
        $current = &$attributes;
        foreach ($keys as $k) {
            if (!is_array($current) || !isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        
        $current = $value;
        $response->sessionAttributes = $attributes;
    }
}

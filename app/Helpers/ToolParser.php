<?php

namespace App\Helpers;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use InvalidArgumentException;

class ToolParser
{
    public static function getToolsForMultipleClasses(array $classes): array
    {
        $tools = [];

        foreach ($classes as $class) {
            if (!class_exists($class)) {
                throw new InvalidArgumentException("Class $class does not exist.");
            }

            $tools = array_merge($tools, self::getToolsForClass($class));
        }

        return $tools;
    }

    public static function getToolRunners(array $classes): array
    {
        $runners = [];

        foreach ($classes as $class) {
            if (!class_exists($class)) {
                throw new InvalidArgumentException("Class $class does not exist.");
            }

            $reflection = new ReflectionClass($class);
            $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if ($method->name === 'getTools') {
                    continue;
                }

                if (!$method->isStatic()) {
                    throw new RuntimeException("Method {$class}::{$method->name} must be static.");
                }

                $toolName = $method->name;

                if (isset($runners[$toolName])) {
                    throw new RuntimeException("Duplicate tool name: $toolName.");
                }

                $runners[$toolName] = fn(...$args) => $class::$toolName(...$args);
            }
        }

        return $runners;
    }

    public static function getToolsForClass(string $className): array
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Class $className does not exist.");
        }

        $reflection = new ReflectionClass($className);
        $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);

        $tools = [];

        foreach ($methods as $method) {
            if ($method->name === 'getTools') {
                continue;
            }

            $docComment = $method->getDocComment();
            $description = '';
            $params = [];

            if (!$docComment) {
                throw new RuntimeException("Method {$method->name} in {$className} is missing a PHPDoc comment.");
            }

            if (!preg_match('/\*\s+(.*?)\n/', $docComment, $descMatch)) {
                throw new RuntimeException("Method {$method->name} in {$className} must have a description.");
            }

            $description = trim($descMatch[1]);

            // Extract @param types
            if (preg_match_all('/@param\s+([^\s]+)\s+\$([^\s]+)(.*?)\n/', $docComment, $paramMatches, PREG_SET_ORDER)) {
                foreach ($paramMatches as $param) {
                    $paramType = $param[1];
                    $paramName = $param[2];
                    $paramDescription = trim($param[3]);

                    if (!$paramType || !$paramName) {
                        throw new RuntimeException("Invalid @param definition in method {$method->name}.");
                    }

                    $params[$paramName] = [
                        'type' => self::mapType($paramType),
                        'description' => $paramDescription ?: 'No description provided',
                    ];
                }
            }

            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $method->name,
                    'description' => $description,
                    'parameters' => [
                        'type' => 'object',
                        'properties' => empty($params) ? new \stdClass() : $params,
                        'required' => array_keys($params),
                    ],
                ]
            ];
        }

        return $tools;
    }

    private static function mapType(string $type): string
    {
        return match (strtolower($type)) {
            'int', 'integer' => 'integer',
            'bool', 'boolean' => 'boolean',
            'float', 'double' => 'number',
            'string' => 'string',
            default => throw new RuntimeException("Unsupported parameter type: $type"),
        };
    }
}
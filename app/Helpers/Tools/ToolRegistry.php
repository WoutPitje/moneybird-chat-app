<?php

namespace App\Helpers\Tools;

use App\Helpers\LLM\ToolParser;

class ToolRegistry
{
    private static array $tools = [MoneyBirdContacts::class];
    
    public static function getTools()
    {
        return ToolParser::getToolsForMultipleClasses(self::$tools);
    }


    public static function runTool(string $functionName, array $parameters)
    {
        $runners = ToolParser::getToolRunners(self::$tools);
        
        // Call the function with individual parameters instead of passing the whole array
        return $runners[$functionName](...array_values($parameters));
    }
}
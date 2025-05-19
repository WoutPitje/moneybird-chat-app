<?php

namespace App\Helpers\Tools;

interface ToolboxInterface
{
    public static function getTools();
    public static function runTool($toolName, $toolParameters);
    public static function hasTool($toolName);
}
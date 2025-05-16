<?php

namespace App\Helpers\LLM;

use App\Helpers\LLM\ToolParser;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class Agent
{
    public array $tools;
    public string $model;
    public float $temperature;
    public array $messages;
    public $chat;

    public static function create(array $tools, string $systemPrompt = null)
    {
        $agent = new Agent();
        $agent->tools = $tools;
        $agent->model = 'gpt-3.5-turbo';
        $agent->temperature = 0.5;
        $agent->messages = [];
        if($systemPrompt) {
            $agent->messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }
        
        return $agent;
    }

    public function sendMessage(string $message, string $role)
    {
        $this->addUserMessage($message);

        $responseMessage = null;
        
        while($responseMessage === null) {
            $response = $this->getOpenAIResponse();

            if($response->choices[0]->finishReason === 'tool_calls') {
                $this->resolveToolCall($response->choices[0]);
            } else {
                $responseMessage = $this->resolveTextMessage($response->choices[0]);
            }
        }

        return $responseMessage;
    }

    private function addAssistantMessage(string|null $message, array|null $toolCalls = null)
    {
        $this->addMessage($message, 'assistant', $toolCalls);
    }

    private function addUserMessage(string $message)
    {
        $this->addMessage($message, 'user');
    }

    private function addToolCallMessage(string $content, string $toolCallId)
    {
        $this->addMessage($content, 'tool', null, $toolCallId);
    }



    private function addMessage(string|null $message, string $role, array|null $toolCalls = null, string|null $toolCallId = null)
    {
        $messageData = [
            'role' => $role,
            'content' => $message,
        ];
        
        if ($toolCalls) {
            $messageData['tool_calls'] = $toolCalls;
        }
        
        if ($toolCallId) {
            $messageData['tool_call_id'] = $toolCallId;
        }
        
        Log::info($messageData);
        $this->messages[] = $messageData;
    }

    private function resolveToolCall($choice)
    {
        $this->addAssistantMessage(null, $choice->message->toolCalls);

        foreach ($choice->message->toolCalls as $toolCall) {
            $toolName = $toolCall->function->name;
            $toolParameters = json_decode($toolCall->function->arguments, true);

            $runner = ToolParser::getToolRunners($this->tools)[$toolName];
            $result = $runner($toolParameters);
            
            $this->addToolCallMessage(json_encode($result), $toolCall->id);
        }
    }

    private function resolveTextMessage($choice)
    {
        $content = $choice->message->content;
        $this->addMessage($content, 'assistant');
        return $content;
    }

    private function getOpenAIResponse()
    {
        $parsedTools = ToolParser::getToolsForMultipleClasses($this->tools);
        $response = OpenAI::chat()->create([
            'model' => $this->model,
            'temperature' => $this->temperature,
            'messages' => $this->messages,
            'tools' => $parsedTools,
        ]);

        return $response;
    }
}
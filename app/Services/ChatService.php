<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Tools\MoneyBirdContacts;
use OpenAI\Laravel\Facades\OpenAI;
use App\Helpers\Tools\ToolRegistry;
use Illuminate\Support\Facades\Cache;

class ChatService
{
    public function getResponse(string $message)
    {
        $threadId = '123';
       
        $tools = ToolRegistry::getTools();

        $thread = [];
        $thread[] = [
            'role' => 'user',
            'content' => $message,
        ];
        
        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $thread,
            'tools' => $tools,
        ]);

        if($response->choices[0]->finishReason === 'tool_calls') {
            $choice = $response->choices[0];
            
            // Add the assistant message with tool_calls first
            $thread[] = [
                'role' => 'assistant',
                'content' => null,
                'tool_calls' => $choice->message->toolCalls,
            ];
            
            foreach ($choice->message->toolCalls as $toolCall) {
                $toolName = $toolCall->function->name;
                $toolParameters = json_decode($toolCall->function->arguments, true);

                $result = ToolRegistry::runTool($toolName, $toolParameters);

                // Add the tool response
                $thread[] = [
                    'role' => 'tool',
                    'content' => json_encode($result),
                    'tool_call_id' => $toolCall->id,
                ];
            }
        }

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $thread,
            'tools' => $tools,
        ]);
        
        $thread[] = [
            'role' => 'assistant',
            'content' => $response->choices[0]->message->content,
        ];
    
        
        return $thread;
    }
    
}

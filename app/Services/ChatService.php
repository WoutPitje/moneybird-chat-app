<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Tools\MoneyBirdContacts;
use OpenAI\Laravel\Facades\OpenAI;
use App\Helpers\Tools\ToolRegistry;
use Illuminate\Support\Facades\Cache;
use App\Helpers\LLM\Agent;

class ChatService
{
    public function getResponse(string $message)
    {
       
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

    public function sendMessage(string $message)
    {
        $tools = [MoneyBirdContacts::class];
        $systemPrompt = 'You are a helpful assistant that can help with tasks related to Moneybird. Allways ask the user if they are sure they want to do something before you do it. If the user says yes, then you should do it.';
        $messages = session('chat', []);
        $agent = Agent::create($tools, $systemPrompt);
        $agent->messages = $messages;
        $agent->sendMessage($message, 'user');

        session(['chat' => $agent->messages]);
        return $agent->messages;
    }
    
}

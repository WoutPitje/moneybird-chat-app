<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Tools\MoneyBirdContacts;
use OpenAI\Laravel\Facades\OpenAI;
use App\Helpers\Tools\ToolRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

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

                dd($toolCall->function);
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
    
    /**
     * Helper method to safely emit SSE data
     *
     * @param array $data
     * @return void
     */
    private function emitSSE(array $data)
    {
        echo "data: " . json_encode($data) . "\n\n";
        
        // Safer flush approach
        if (ob_get_length() > 0) {
            ob_flush();
        }
        flush();
    }
    
    /**
     * Stream the chat response
     *
     * @param string $message
     * @return \Illuminate\Http\Response
     */
    public function streamResponse(string $message)
    {
        try {
            $tools = ToolRegistry::getTools();
    
            $thread = [];
            $thread[] = [
                'role' => 'user',
                'content' => $message,
            ];
            
            // Set up the streaming response
            return response()->stream(function () use ($thread, $tools) {
                try {
                    // Start output buffering to ensure we can flush
                    if (ob_get_level() == 0) {
                        ob_start();
                    }
                    
                    // First check if we need to call any tools
                    $response = OpenAI::chat()->create([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => $thread,
                        'tools' => $tools,
                    ]);
            
                    if($response->choices[0]->finishReason === 'tool_calls') {
                        $choice = $response->choices[0];
                        
                        // Stream that tools are being called
                        foreach ($choice->message->toolCalls as $index => $toolCall) {
                            $toolName = $toolCall->function->name;
                            $toolParameters = json_decode($toolCall->function->arguments, true);
                            
                            // Stream that a tool is being called
                            $this->emitSSE([
                                'type' => 'tool_call',
                                'tool' => [
                                    'name' => $toolName,
                                    'id' => $toolCall->id,
                                    'parameters' => $toolParameters
                                ]
                            ]);
                            // Run the tool
                            $result = ToolRegistry::runTool($toolName, $toolParameters);
                            
                            // Stream the tool result
                            $this->emitSSE([
                                'type' => 'tool_result',
                                'tool' => [
                                    'name' => $toolName,
                                    'id' => $toolCall->id,
                                    'result' => $result
                                ]
                            ]);
                            
                            // Add the assistant message with tool_calls to the thread
                            if ($index === 0) {
                                $thread[] = [
                                    'role' => 'assistant',
                                    'content' => null,
                                    'tool_calls' => $choice->message->toolCalls,
                                ];
                            }
                            
                            // Add the tool response to the thread
                            $thread[] = [
                                'role' => 'tool',
                                'content' => json_encode($result),
                                'tool_call_id' => $toolCall->id,
                            ];
                        }
                    }
                    
                    // Stream the final model response
                    $this->emitSSE(['type' => 'thinking']);
                    
                    $stream = OpenAI::chat()->createStreamed([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => $thread,
                        'tools' => $tools,
                    ]);
                    
                    foreach ($stream as $response) {
                        if (isset($response->choices[0]->delta->content)) {
                            $text = $response->choices[0]->delta->content;
                            $this->emitSSE([
                                'type' => 'content',
                                'content' => $text
                            ]);
                        }
                    }
                    
                    $this->emitSSE(['type' => 'done']);
                    
                    // Clean up output buffering
                    if (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    
                } catch (Exception $e) {
                    Log::error('Streaming error: ' . $e->getMessage());
                    
                    $this->emitSSE([
                        'type' => 'error',
                        'content' => 'Error: ' . $e->getMessage()
                    ]);
                    
                    $this->emitSSE(['type' => 'done']);
                    
                    // Clean up output buffering
                    if (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                }
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Connection' => 'keep-alive',
            ]);
        } catch (Exception $e) {
            Log::error('Chat service error: ' . $e->getMessage());
            
            // Return error as a stream to maintain the same interface
            return response()->stream(function () use ($e) {
                // Start output buffering to ensure we can flush
                if (ob_get_level() == 0) {
                    ob_start();
                }
                
                $this->emitSSE([
                    'type' => 'error',
                    'content' => 'Error: ' . $e->getMessage()
                ]);
                
                $this->emitSSE(['type' => 'done']);
                
                // Clean up output buffering
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
                
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Connection' => 'keep-alive',
            ]);
        }
    }
}

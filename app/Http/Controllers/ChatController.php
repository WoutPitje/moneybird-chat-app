<?php

namespace App\Http\Controllers;

use App\Services\MoneybirdAuthService;
use App\Helpers\Moneybird;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ChatService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{

    public function __construct(private MoneybirdAuthService $moneybirdAuthService, private ChatService $chatService)
    {
    }
  
    public function index()
    {
        $administrationId = $this->moneybirdAuthService->getAdministrationId();
        $administration = Moneybird::getMoneybird()->administration()->get(['id' => $administrationId]);

        

        return Inertia::render('Chat', [
            'administration' => $administration,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $message = $request->input('message');
        $threadId = Str::uuid();
        $messages = $this->chatService->getResponse($message, $threadId);
        
        return Inertia::render('Chat', [
            'messages' => $messages,
        ]);
    }
    
    /**
     * Stream a chat message response
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function streamMessage(Request $request)
    {
        try {
            $message = $request->input('message') ?? $request->query('message');
            
            $response = $this->chatService->streamResponse($message);
            
            // Add CORS headers for compatibility
            foreach ([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Allow-Credentials' => 'true',
            ] as $key => $value) {
                $response->headers->set($key, $value);
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Stream controller error: ' . $e->getMessage());
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

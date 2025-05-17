<?php

namespace App\Http\Controllers;

use App\Services\MoneybirdAuthService;
use App\Helpers\Moneybird;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ChatService;
use Illuminate\Support\Str;

class ChatController extends Controller
{

    public function __construct(private MoneybirdAuthService $moneybirdAuthService, private ChatService $chatService)
    {
    }
  
    public function index()
    {
        $administrationId = $this->moneybirdAuthService->getAdministrationId();
        $administration = Moneybird::getMoneybird()->administration()->get(['id' => $administrationId]);

        $messages = session('chat', []);

        return Inertia::render('Chat', [
            'administration' => $administration,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $message = $request->input('message');
        $messages = $this->chatService->sendMessage($message);
        
        return Inertia::render('Chat', [
            'messages' => $messages,
        ]);
    }

    public function clearChat()
    {
        session()->forget('chat');
        return redirect()->back();
    }
}

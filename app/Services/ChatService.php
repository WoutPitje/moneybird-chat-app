<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\LLM\Agent;
use App\Helpers\Tools\Moneybird\MoneybirdContactPeopleToolbox;
use App\Helpers\Tools\Moneybird\MoneyBirdContactsToolbox;
use App\Helpers\Tools\Moneybird\PurchaseInvoiceToolbox;
class ChatService
{

    public function sendMessage(string $message)
    {
        $tools = [MoneyBirdContactsToolbox::class, MoneybirdContactPeopleToolbox::class, PurchaseInvoiceToolbox::class];
        $systemPrompt = 'You are a helpful assistant that can help with tasks related to Moneybird. Allways ask for permission when using a tool or using a function. Only give answers in context of moneybird. Always ask for all information you need before using a tool. Allways ask permission when using a tool or making changes to the data. Never make data up, only if asked for it. Praat altijd in het nederlands, tenzij de gebruiker anders vraagt.';
        $messages = session('chat', [['role' => 'system', 'content' => $systemPrompt]]);

        $agent = Agent::create($tools, $systemPrompt);
        $agent->messages = $messages;
        $agent->sendMessage($message, 'user');

        session(['chat' => $agent->messages]);
        return $agent->messages;
    }
    
}

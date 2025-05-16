<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\CssSelector\Node\FunctionNode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Helpers\Moneybird;

class MoneybirdAuthService
{

    public function login()
    {

        return Moneybird::getConnection()->getAuthUrl();
        
    }

    public function logout()
    {
        $session = session();
        $session->forget('moneybird_access_token');
        $session->forget('moneybird_administration_id');
    }

    public function callback(string $code)
    {
        Moneybird::setAuthorizationCode($code);
        $moneybird = Moneybird::getMoneybird();
        $administrations = $moneybird->administration()->getAll();
        $firstAdministration = $administrations[0];
        Moneybird::setAdministrationId($firstAdministration->id);
        $moneybird = Moneybird::getMoneybird();

        $moneybirdUser = $moneybird->user()->get()[0];

        $user = User::where('email', $moneybirdUser->email)->first();
        if (!$user) {
            $user = User::create([
                'email' => $moneybirdUser->email,
                'password' => Hash::make(Str::random(10)),
            ]);
        }

        Auth::login($user);
    }



    public function getAdministrationId()
    {
        $session = session();
        return $session->get('moneybird_administration_id');
    }


    
}
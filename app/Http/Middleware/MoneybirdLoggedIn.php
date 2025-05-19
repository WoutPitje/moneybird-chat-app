<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\Moneybird;
class MoneybirdLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('moneybird_access_token')) {
            try {
                $moneybird = Moneybird::getMoneybird();
            } catch (\Exception $e) {
                return redirect()->route('welcome');
            }

            return redirect()->route('welcome');
        }
        return $next($request);
    }
}
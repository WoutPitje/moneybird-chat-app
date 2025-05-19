<?php

namespace App\Http\Controllers;

use App\Services\MoneybirdAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class MoneybirdAuthController extends Controller
{
    public function __construct(private MoneybirdAuthService $moneybirdAuthService)
    {
    }

    public function login(MoneybirdAuthService $moneybirdAuthService)
    {
        return Redirect::away($moneybirdAuthService->login());
    }

    public function callback(MoneybirdAuthService $moneybirdAuthService, Request $request)
    {
        if ($request->get('code')) {
            $moneybirdAuthService->callback($request->get('code'));
            return redirect()->route('chat');
        }
        return redirect()->route('welcome');
    }

    public function logout(MoneybirdAuthService $moneybirdAuthService)
    {
        $moneybirdAuthService->logout();
        return redirect()->route('welcome');
    }
}
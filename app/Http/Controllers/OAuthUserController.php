<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;

class OAuthUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->only(['login', 'callback']);
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
        $referer = $request->headers->get('referer');
        Redirect::setIntendedUrl($referer);

        return Socialite::driver('mediawiki')->redirect();
    }

    public function callback()
    {
        $socialiteUser = Socialite::driver('mediawiki')->user();

        $user = User::firstOrCreate([
            'username' => $socialiteUser->name,
            'mw_userid' => $socialiteUser->id
        ]);

        Auth::login($user, false);
        return redirect()->intended();
    }

    public function logout(Request $request)
    {
        $referer = $request->headers->get('referer');
        Auth::guard()->logout();

        $request->session()->invalidate();
        return redirect($referer);
    }
}

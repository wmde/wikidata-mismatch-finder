<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApiTokenController extends Controller
{
    public function showToken(Request $request)
    {        
        return view('showToken', [ 'tokens' => $request->user()->tokens ]);
    }

    public function createToken(Request $request) {
        if( sizeof($request->user()->tokens) > 0 ) {
            // token already exists
            return redirect( route('token') );
        }

        $token = $request->user()->createToken('apiToken');
        return view('newToken', [ 'newToken' => $token->plainTextToken ]);
    }

    public function revokeToken(Request $request) {
        Auth::user()->tokens()->where('id', $request->id)->delete();

        return redirect( route('token') );
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ApiTokenController extends Controller
{
    public function showToken(Request $request)
    {
        if (!Auth::check()) {
            return view('welcome');
        }

        $upload_permission = false;

        if (Gate::allows('upload-import')) {
            $upload_permission = true;
        }

        return view('showToken', [ 'tokens' => $request->user()->tokens, 'upload_permission' => $upload_permission ]);
    }

    public function createToken(Request $request)
    {
        if (sizeof($request->user()->tokens) > 0) {
            // token already exists and we want to create a new one
            // we delete the previous tokens
            Auth::user()->tokens()->delete();
        }

        $token = $request->user()->createToken('apiToken');

        return redirect(route('api.settings'))->with('flashToken', $token->plainTextToken);
    }

    public function revokeToken(Request $request)
    {
        Auth::user()->tokens()->where('id', $request->id)->delete();

        return redirect(route('api.settings'));
    }
}

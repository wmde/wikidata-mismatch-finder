<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function __construct() {
        $this->middleware('auth')->only('upload');
    }

    public function upload(Request $request) {
        if(!$request->hasFile('mismatchFile')) {
            return response( ['success' => False, 'reason' => 'no attached mismatch file found' ], 400);
        }

        $file = $request->file('mismatchFile');

        if(!$file->isValid()) {
            return response( ['success' => False, 'reason' => 'validation of attached mismatch file failed' ], 400);
        }

        $file->storeAs('mismatch-files', 'mimsmatch-upload.' . $request->user()->mw_userid . '.csv');

        return response( [
            'success' => 'true'
        ], 201);
    }
}

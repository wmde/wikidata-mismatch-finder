<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        if (!$request->user()->canUpload()) {
            return response(['success' => false, 'reason' => 'User has no upload privilege' ], 403);
        }

        $request->validate([
            'mismatchFile' => [ 'required', 'file', 'max:' . env('UPLOAD_SIZE_LIMIT'), 'mimes:csv,txt' ]
        ]);

        $uploadName = $request->name;
        $description = $request->description;
        $filename = date('Ymd_His') . '-mismatch-upload.' . $request->user()->mw_userid . '.csv';
        $request->file('mismatchFile')->storeAs('mismatch-files', $filename);

        return response([
            'uploadName' => $uploadName,
            'description' => $description,
            'filename' => $filename,
            'success' => 'true'
        ], 201);
    }
}

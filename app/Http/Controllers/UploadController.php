<?php

namespace App\Http\Controllers;

use App\Models\UploadUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        Gate::authorize('upload-import');

        $request->validate([
            'mismatchFile' => [ 'required', 'file', 'size:' . config('filesystems.uploads.max_size'), 'mimes:csv,txt' ]
        ]);

        $uploadName = $request->name;
        $description = $request->description;
        $filename = now()->format('Ymd_His') . '-mismatch-upload.' . $request->user()->mw_userid . '.csv';
        $request->file('mismatchFile')->storeAs('mismatch-files', $filename);

        return response([
            'uploadName' => $uploadName,
            'description' => $description,
            'filename' => $filename,
            'success' => 'true'
        ], 201);
    }
}

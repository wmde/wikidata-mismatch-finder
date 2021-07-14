<?php

namespace App\Http\Controllers;

use App\Models\UploadUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class ImportController extends Controller
{
    public function upload(Request $request)
    {
        // TODO: Consider using a FormRequest class for auth and validation
        Gate::authorize('upload-import');

        /**
         * TODO: Test and ensure validation messages are sent out
         *
         * | Validation Rule | M | A |
         * |-----------------|---|---|
         * | Large file      | p |   |
         * | Wrong file type | p |   |
         * | Missing file    | p |   |
         * | Missing name    | p |   |
         */
        $request->validate([
            'name' => ['required'],
            'mismatchFile' => [
                'required',
                'file',
                'max:' . config('filesystems.uploads.max_size'), 'mimes:csv,txt'
            ]
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

<?php

namespace App\Http\Controllers;

use App\Models\CollectionAndPosting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CollectionProofController extends Controller
{
    public function show(CollectionAndPosting $record)
    {
        abort_unless(Auth::check(), 403, 'Unauthorized.');
        abort_unless($record->file_path, 404, 'No file attached to this record.');
        abort_unless(
            Storage::disk('private')->exists($record->file_path),
            404,
            'File not found on disk.'
        );

        return Storage::disk('private')->response(
            $record->file_path,
            $record->original_file_name ?? basename($record->file_path),
            ['Cache-Control' => 'private, no-store']
        );
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadExportController extends Controller
{
    public function __invoke(string $fileName): StreamedResponse
    {
        if (! Storage::exists("exports/{$fileName}")) {
            abort(419);
        }

        return response()->streamDownload(function () use ($fileName) {
            echo Storage::get("exports/{$fileName}/headers.csv");

            flush();

            foreach (Storage::files("exports/{$fileName}") as $file) {
                if (str($file)->endsWith('headers.csv')) {
                    continue;
                }

                if (! str($file)->endsWith('.csv')) {
                    continue;
                }

                echo Storage::get($file);

                flush();
            }
        }, "{$fileName}.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }
}

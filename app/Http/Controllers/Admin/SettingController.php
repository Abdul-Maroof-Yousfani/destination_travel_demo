<?php

namespace App\Http\Controllers\Admin;

use App\Models\Airport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function view()
    {
        $airports = Airport::orderBy('name')->get();
        $airlines = ['emirates', 'pia', 'flyjinnah'];
        return view('admin.settings.index', compact('airlines', 'airports'));
    }
    // ----------------------- logs -----------------------
    public function getAvailableDates(Request $request)
    {
        $request->validate(['airline' => 'required|string']);
        $airline = $request->airline;

        $path = storage_path("logs/{$airline}");
        // dd($path);
        $dates = [];

        if (is_dir($path)) {
            $files = scandir($path);
            // dd($files);
            foreach ($files as $file) {
                // Match both "2025_10_23.log" and "bookings_2025_10_23.txt"
                if (preg_match('/(\d{4})_(\d{2})_(\d{2})\.(log|txt)$/', $file, $matches)) {
                    $date = "{$matches[1]}-{$matches[2]}-{$matches[3]}";
                    $type = str_starts_with($file, 'bookings_') ? 'booking' : 'log';
                    $dates[$date][$type] = $file;
                }
            }
        }

        return response()->json(['availableDates' => $dates]);
    }
    public function downloadFile(Request $request)
    {
        $request->validate([
            'airline' => 'required|string',
            'file' => 'required|string'
        ]);

        $filePath = storage_path("logs/{$request->airline}/{$request->file}");
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath);
    }
    // ----------------------- logs -----------------------
}

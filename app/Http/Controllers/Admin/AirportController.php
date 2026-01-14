<?php

namespace App\Http\Controllers\Admin;

use App\Models\Airport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AirportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'order_by' => 'nullable|integer|max:2048',
            'code' => 'required|string|max:10|unique:airports,code',
            'time_zone' => 'required|string|max:255',
            'city_code' => 'required|string|max:10',
            'is_local' => 'boolean|de',
            'country' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
        ]);

        Airport::create($request->all());
        return response()->json(['success' => true]);
    }
    public function list()
    {
        $airports = Airport::whereNotNull('order_by')
            ->where('order_by', '!=', '')
            ->orderBy('order_by', 'asc')
            ->orderBy('name', 'asc')
            ->limit(30)
            ->get();

        return response()->json($airports);
    }
    public function single(Request $request)
    {
        $code = $request->query('code');

        if (!$code) return response()->json(['error' => 'Code is required'], 400);

        $airport = Airport::where('code', $code)->first();

        if (!$airport) return response()->json(['error' => 'Airport not found'], 404);

        return response()->json($airport);
    }
    public function show($id)
    {
        $airport = Airport::findOrFail($id);
        return response()->json($airport);
    }
    public function update(Request $request, $id)
    {
        $airport = Airport::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'order_by' => 'nullable|integer|max:2048',
            'code' => "required|string|max:10|unique:airports,code,{$id}",
            'time_zone' => 'required|string|max:255',
            'city_code' => 'required|string|max:10',
            'is_local' => 'boolean',
            'country' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
        ]);

        $airport->update($request->all());
        return response()->json(['success' => true]);
    }
    public function destroy($id)
    {
        Airport::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}

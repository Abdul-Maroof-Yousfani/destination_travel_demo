<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function show(Client $client)
    {
        $client->load([
            'bookings.flights',
            'bookings.agent',
            'bookings.payments',
            'passengers'
        ]);
        
        $totalBookings = $client->bookings()->count();
        $totalPassengers = $client->passengers()->count();
        $totalRevenue = $client->bookings()
            ->withSum('payments as total_revenue', 'base_price')
            ->get()
            ->sum('total_revenue');

        return view('admin.clients.show', compact(
            'client',
            'totalBookings',
            'totalPassengers',
            'totalRevenue'
        ));
    }
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $clients = $query->latest()->paginate(10);

        return view('admin.clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_code' => 'required|string|max:5',
            'phone' => 'required|string|max:15|unique:clients,phone',
            'email' => 'required|email|max:255|unique:clients,email',
            'password' => 'required|min:6',
            'accept_notification' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['original_password'] = $request->password;

        Client::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Client created successfully']);
    }

    public function edit(Client $client)
    {
        return response()->json($client);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_code' => 'required|string|max:5',
            'phone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('clients', 'phone')->ignore($client->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($client->id),
            ],
            'password' => 'nullable|min:6',
            'accept_notification' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
            $validated['original_password'] = $request->password;
        }

        $client->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Client updated successfully']);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['status' => 'success', 'message' => 'Client deleted successfully']);
    }

    public function toggleStatus(Client $client)
    {
        $client->update(['is_active' => !$client->is_active]);
        return response()->json(['status' => 'success', 'message' => 'Client status updated']);
    }
}

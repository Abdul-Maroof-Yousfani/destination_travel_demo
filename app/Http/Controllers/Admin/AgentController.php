<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class AgentController extends Controller
{
    public function index()
    {
        // $agents = User::role('agent')->orderBy('created_at' ,'desc')->get();
        $roles = Role::all();
        $agents = User::whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.agents.list', compact('agents', 'roles'));
    }

    /**
     * Store a newly created agent in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $agent = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign Spatie role
        $agent->assignRole('agent');

        return redirect()->back()->with('message', 'Agent created successfully.')->with('status', 'success');
    }

    /**
     * Update the specified agent in storage.
     */
    public function update(Request $request, User $agent)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $agent->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $agent->fill([
            'name' => $validated['name'] ?? $agent->name,
            'email' => $validated['email'] ?? $agent->email,
        ]);

        if (!empty($validated['password'])) {
            $agent->password = Hash::make($validated['password']);
        }

        $agent->save();

        return redirect()->back()->with('message', 'Agent updated successfully.')->with('status', 'success');
    }

    /**
     * Remove the specified agent from storage.
     */
    public function destroy(User $agent)
    {
        if (!$agent->hasRole('agent')) {
            return redirect()->back()->with('error', 'This user is not an agent.');
        }

        $agent->delete();

        return redirect()->back()->with('message', 'Agent deleted successfully.')->with('status', 'success');
    }

    /**
     * Login as the specified agent.
     */
    public function loginAs(User $agent)
    {
        if (!$agent) return redirect()->back()->with('error', 'This user is not found.');

        Auth::login($agent);

        return redirect()->route('admin.dashboard')->with('message', 'Logged in as agent.')->with('status', 'success');
    }

    // public function editPermission(User $agent)
    // {
    //     $permissions = Permission::all();
    //     $roles = Role::all();

    //     return view('admin.agents.edit', compact('agent', 'permissions', 'roles'));
    // }

    public function updatePermissions(Request $request, User $agent)
    {
        $request->validate([
            'permissions' => 'array',
            'roles' => 'array'
        ]);

        $agent->syncPermissions($request->permissions ?? []);
        $agent->syncRoles($request->roles ?? []);

        return redirect()->route('admin.agents.index')->with('success', 'Permissions updated.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('provinsi');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15);

        // Statistics
        $statistics = [
            'total' => User::count(),
            'super_admins' => User::where('role', 'super_admin')->count(),
            'admin_vips' => User::where('role', 'admin_vip')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return Inertia::render('Users/Index', [
            'users' => $users,
            'statistics' => $statistics,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    public function create()
    {
        $provinsiList = Provinsi::orderBy('nama')->get();
        
        return Inertia::render('Users/Create', [
            'provinsiList' => $provinsiList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
            'provinsi_id' => 'nullable|exists:provinsi,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('provinsi');
        
        return Inertia::render('Users/Show', [
            'user' => $user
        ]);
    }

    public function edit(User $user)
    {
        $user->load('provinsi');
        $provinsiList = Provinsi::orderBy('nama')->get();
        
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'provinsiList' => $provinsiList,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
            'provinsi_id' => 'nullable|exists:provinsi,id',
        ]);

        // Don't allow users to demote themselves from admin
        if ($user->id === auth()->id() && $user->isAdmin() && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot change your own admin role.']);
        }

        // Don't allow users to deactivate themselves
        if ($user->id === auth()->id() && !($validated['is_active'] ?? true)) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Don't allow users to delete themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        // Don't allow deletion of the last admin
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['user' => 'Cannot delete the last admin user.']);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        // Don't allow users to deactivate themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot change your own account status.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$status} successfully.");
    }
}

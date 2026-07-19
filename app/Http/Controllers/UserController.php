<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $allUsers = User::whereIn('role', [Role::ADMIN, Role::ATTENDANCE_CHECKER, Role::SUPER_ADMIN])
            ->orderBy('last_seen_at', 'desc')
            ->get();

        return view('admin.user-management', compact('allUsers'));
    }

    public function create()
    {
        return view('admin.create-user');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'role' => ['required', Rule::in([Role::ADMIN])],
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'password.regex' => 'Password must contain at least one uppercase, lowercase, number, and special character.',
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => Role::ADMIN,
            'status'    => 'active', // IDAGDAG: Set default status to 'active'
        ]);

        return redirect()->route('admin.user-management')->with('success', 'New Administrator account created successfully!');
    }
    
    // --- START: New Status Management Functions ---
    
    /**
     * Display the form to edit the user's status (optional, but good practice)
     * You would typically use a modal or a simple edit button for this in the view.
     */
    public function edit(User $user)
    {
        // Add Authorization/Gate check if only SUPER_ADMIN can edit status
        // Gate::authorize('update-status', $user); 
        
        return view('admin.edit-user-status', compact('user'));
    }

    /**
     * Update the user's status.
     */
    public function updateStatus(Request $request, User $user)
    {
        // Add Authorization/Gate check if only SUPER_ADMIN can update status
        // Gate::authorize('update-status', $user);

        // Kukunin ang bagong status. Palitan ang 'active', 'suspended', 'disabled' batay sa iyong enum o values.
        $validStatuses = ['active', 'suspended', 'disabled'];

        $request->validate([
            'status' => ['required', Rule::in($validStatuses)],
        ]);

        $user->update([
            'status' => $request->status,
        ]);

        return redirect()->route('admin.user-management')->with('success', "User's status updated to {$request->status} successfully.");
    }
    
    // --- END: New Status Management Functions ---

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->with('roles')
            ->latest()
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'roles' => $u->roles->pluck('name')->values()->all(),
                'created_at' => optional($u->created_at)->format('Y-m-d H:i:s'),
            ])
            ->values()
            ->all();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'availableRoles' => ['admin', 'engineer'],
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,engineer'],
        ]);

        // optional safety: cegah admin ubah role dirinya sendiri
        if ($request->user()?->id === $user->id) {
            return back()->with('error', 'Kamu tidak bisa mengubah role akunmu sendiri.');
        }

        $user->syncRoles([$validated['role']]);

        return back()->with('success', "Role {$user->name} diubah ke {$validated['role']}.");
    }
}
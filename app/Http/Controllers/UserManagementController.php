<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                // ilike only exists in PostgreSQL; the demo/test database (SQLite) uses like
                $op = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $query->where(function ($q) use ($like, $op) {
                    $q->where('name', $op, $like)
                        ->orWhere('email', $op, $like)
                        ->orWhere('student_number', $op, $like);
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::enum(Role::class)],
            'student_number' => ['nullable', 'string', 'max:30', Rule::unique('users', 'student_number')->ignore($user->id)],
            'course' => ['nullable', Rule::in(config('campus.courses'))],
            'year_level' => ['nullable', 'integer', 'between:1,6'],
            'section' => ['nullable', 'string', 'max:10'],
        ]);

        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own admin role. Ask another admin to do it.'])->withInput();
        }

        $oldRole = $user->role;
        $section = strtoupper(trim((string) ($data['section'] ?? '')));

        // role is not mass-assignable on purpose, so it is set explicitly
        $user->role = $data['role'];
        $user->fill([
            'student_number' => $data['student_number'] ?? null,
            'course' => $data['course'] ?? null,
            'year_level' => $data['year_level'] ?? null,
            'section' => $section !== '' ? $section : null,
        ]);
        $user->save();

        if ($oldRole !== $user->role) {
            Log::info('User role changed', [
                'by' => $request->user()->id,
                'user' => $user->id,
                'from' => $oldRole,
                'to' => $user->role,
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', "{$user->name} updated.");
    }
}

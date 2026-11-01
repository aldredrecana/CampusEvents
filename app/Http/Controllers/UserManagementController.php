<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'ilike', $like)
                      ->orWhere('email', 'ilike', $like)
                      ->orWhere('student_number', 'ilike', $like);
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
            'role' => ['required', Rule::in(['student', 'organizer', 'admin'])],
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
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required', 'string', 'lowercase', 'email', 'max:255',
            'unique:' . User::class,
            function ($attribute, $value, $fail) {
                $domain = config('campus.allowed_email_domain');
                if ($domain && ! str_ends_with($value, '@' . $domain)) {
                    $fail("Please use your school email (@{$domain}).");
                }
            },
        ],
        'student_number' => ['required', 'string', 'max:30', 'unique:users,student_number'],
        'course' => ['required', 'in:' . implode(',', config('campus.courses'))],
        'year_level' => ['required', 'integer', 'between:1,6'],
        'section' => ['required', 'string', 'max:10'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'student_number' => $request->student_number,
        'course' => $request->course,
        'year_level' => $request->year_level,
        'section' => strtoupper(trim($request->section)),
    ]);

    event(new Registered($user));

    Auth::login($user);

    return redirect(route('dashboard', absolute: false));
}
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MasterlistStudent;
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
        // "2023-00123-cl-0 " becomes "2023-00123-CL-0" before checking
        $request->merge(['student_number' => strtoupper(trim((string) $request->student_number))]);

        $studentNumberRules = ['required', 'string', 'max:30', 'regex:'.config('campus.student_number_pattern'), 'unique:users,student_number'];
        if (config('campus.require_masterlist')) {
            $studentNumberRules[] = 'exists:masterlist_students,student_number';
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                'unique:'.User::class,
                function ($attribute, $value, $fail) {
                    $domain = config('campus.allowed_email_domain');
                    if ($domain && ! str_ends_with($value, '@'.$domain)) {
                        $fail("Please use your school email (@{$domain}).");
                    }
                },
            ],
            'student_number' => $studentNumberRules,
            'course' => ['required', 'in:'.implode(',', config('campus.courses'))],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'section' => ['required', 'string', 'max:10'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'student_number.regex' => 'Use your PUP student number format, e.g. '.config('campus.student_number_example').'.',
            'student_number.exists' => 'This student number is not in the official student list. Please contact the admin.',
            'student_number.unique' => 'This student number is already registered.',
        ]);

        // If the registrar's masterlist has this student, its course/year/section wins
        $official = MasterlistStudent::where('student_number', $request->student_number)->first();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'student_number' => $request->student_number,
            'course' => $official->course ?? $request->course,
            'year_level' => $official->year_level ?? $request->year_level,
            'section' => $official->section ?? strtoupper(trim($request->section)),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}

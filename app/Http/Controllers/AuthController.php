<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

// Handler sınıfları
use App\Chain\SendWelcomeEmailHandler;
use App\Chain\LogRegistrationHandler;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->withInput($request->only('email', 'remember'));
    }

    public function register(Request $request)
    {
        // Validate form data
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Create user
        $user = User::create($validated);

        // Login user
        Auth::login($user);

        // Create pipeline handlers
        $pipeline = [
            new SendWelcomeEmailHandler(),
            new LogRegistrationHandler(),
        ];

        // Prepare payload
        $payload = ['user' => $user];

        // Run pipeline
        foreach ($pipeline as $handler) {
            $payload = $handler->handle($payload);
        }

        // Fire event
        event(new \App\Events\UserRegistered($user));

        return redirect()->route('books.index');
    }
}

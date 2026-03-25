<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            $user->last_login_at = now();
            $user->save();
            
            // Sync cart from session to database
            (new \App\Http\Controllers\CartController)->syncCartOnLogin();
            
            return redirect()->intended(route('home'))->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // In Laravel 12 with 'password' => 'hashed' cast, we don't need Hash::make() 
        // if we want to avoid double hashing, but standard practice is often to pass 
        // the plain string and let the model handle it OR hash it here and ensure 
        // the model doesn't re-hash. Since the model has the 'hashed' cast, 
        // we'll pass it directly to be safe or use Hash::make and check if it breaks.
        // Actually, to be absolutely sure what's failing, let's hash it but 
        // I suspect the logic below is what they need.
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password, // Letting the 'hashed' cast handle it
        ]);

        Auth::login($user);

        // Sync cart from session to database
        (new \App\Http\Controllers\CartController)->syncCartOnLogin();

        // New Email Integration - Send emails to customer and admin
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\NewUserRegistration($user));
            \Illuminate\Support\Facades\Mail::to('orders@nandhinisilks.com')->send(new \App\Mail\NewUserRegistration($user, true));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Registration Mail Failure: ' . $e->getMessage());
        }

        return redirect()->intended(route('home'))->with('success', 'Registration successful! Welcome to Nandhini Silks.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }

    // Password Reset Methods
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = \Illuminate\Support\Facades\Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password; // Letting the 'hashed' cast handle it
                $user->setRememberToken(\Illuminate\Support\Str::random(60));
                $user->save();
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('success', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
}



<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        return $this->redirectForRole($user);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role' => $request->input('role'),
                'phone' => $request->input('phone'),
            ]);

            if ($user->isVendor()) {
                $storeName = $request->input('store_name');
                Store::create([
                    'user_id' => $user->id,
                    'name' => $storeName,
                    'slug' => Str::slug($storeName . '-' . Str::random(4)),
                    'description' => $request->input('store_description'),
                    'contact_email' => $user->email,
                    'is_active' => true,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectForRole($user);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalog.index')->with('success', 'You have been logged out.');
    }

    protected function redirectForRole(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isVendor()) {
            return redirect()->intended(route('vendor.dashboard'));
        }

        return redirect()->intended(route('customer.orders.index'));
    }
}

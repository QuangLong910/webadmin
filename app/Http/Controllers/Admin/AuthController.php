<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThueXeApi;
use App\Support\Jwt;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has(ThueXeApi::SESSION_TOKEN)) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request, ThueXeApi $api)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], ['phone' => 'số điện thoại', 'password' => 'mật khẩu']);

        $res = $api->login($data['phone'], $data['password']);
        $token = $res['token'] ?? null;

        if (! $token) {
            return back()->withInput()->with('error', 'Backend không trả token.');
        }

        // Chỉ người vận hành mới vào được trang quản trị; backend cũng chặn /admin/** nhưng báo sớm cho rõ.
        if (! in_array('VAN_HANH', Jwt::roles($token), true)) {
            return back()->withInput()->with('error', 'Tài khoản này không có vai VAN_HANH nên không vào được trang quản trị.');
        }

        $request->session()->regenerate();
        $request->session()->put(ThueXeApi::SESSION_TOKEN, $token);
        $request->session()->put(ThueXeApi::SESSION_PROFILE, $res['profile'] ?? []);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

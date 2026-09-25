<?php

namespace App\Http\Middleware;

use App\Services\ThueXeApi;
use App\Support\Jwt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chặn các trang quản trị khi chưa đăng nhập hoặc token backend đã hết hạn.
 */
class EnsureApiLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session()->get(ThueXeApi::SESSION_TOKEN);

        if (! $token || Jwt::isExpired($token)) {
            $request->session()->forget([ThueXeApi::SESSION_TOKEN, ThueXeApi::SESSION_PROFILE]);

            return redirect()->guest(route('login'))
                ->with('error', $token ? 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại.' : null);
        }

        return $next($request);
    }
}

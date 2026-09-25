<?php

namespace App\Exceptions;

use App\Services\ThueXeApi;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Lỗi nghiệp vụ 422 (WRONG_STATE, INVALID_INPUT…) là chuyện bình thường, không ghi log.
        $this->reportable(function (ApiException $e) {
            if ($e->status === 422) {
                return false;
            }
        });

        // Lỗi backend: hết phiên → về trang đăng nhập; thao tác ghi → quay lại form kèm thông báo;
        // trang xem → hiện trang lỗi thay vì màn hình trắng.
        $this->renderable(function (ApiException $e, Request $request) {
            if ($e->isUnauthenticated()) {
                $request->session()->forget([ThueXeApi::SESSION_TOKEN, ThueXeApi::SESSION_PROFILE]);

                return redirect()->route('login')->with('error', $e->friendlyMessage());
            }

            if (! $request->isMethod('GET')) {
                return back()->withInput()->with('error', $e->friendlyMessage());
            }

            return response()->view('errors.api', ['error' => $e], $e->status === 0 ? 503 : 502);
        });
    }
}

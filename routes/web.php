<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\LedgerController;
use App\Http\Controllers\Admin\PayoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web quản trị sàn thuê xe
|--------------------------------------------------------------------------
|
| Mọi dữ liệu lấy từ backend qua App\Services\ThueXeApi. Luồng việc của người
| vận hành: duyệt giấy tờ → duyệt xe → xác nhận tiền vào → quyết toán → chi trả
| → đối soát sổ cái.
|
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('admin.auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/{id}/review', [DocumentController::class, 'review'])->whereNumber('id')->name('documents.review');

    Route::get('/cars', [CarController::class, 'index'])->name('cars.index');
    Route::post('/cars/{id}/review', [CarController::class, 'review'])->whereNumber('id')->name('cars.review');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/find', [BookingController::class, 'find'])->name('bookings.find');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->whereNumber('id')->name('bookings.show');
    Route::post('/bookings/{id}/confirm-payment', [BookingController::class, 'confirmPayment'])->whereNumber('id')->name('bookings.confirm-payment');
    Route::post('/bookings/{id}/settle', [BookingController::class, 'settle'])->whereNumber('id')->name('bookings.settle');

    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{id}/paid', [PayoutController::class, 'paid'])->whereNumber('id')->name('payouts.paid');

    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger.index');
});

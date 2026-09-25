<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThueXeApi;

class DashboardController extends Controller
{
    public function __invoke(ThueXeApi $api)
    {
        $payouts = $api->payouts('CHO');

        return view('dashboard', [
            'docCount' => count($api->documents('CHO_DUYET')),
            'carCount' => count($api->cars('CHO_DUYET')),
            'unpaidCount' => count($api->bookings('CHO_THANH_TOAN')),
            'settleCount' => count($api->bookings('CHO_QUYET_TOAN')),
            'payoutCount' => count($payouts),
            'payoutTotal' => array_sum(array_column($payouts, 'amount')),
            'payoutNoBank' => count(array_filter($payouts, fn ($p) => ($p['bankAccount'] ?? null) === 'CHUA_CO')),
        ]);
    }
}

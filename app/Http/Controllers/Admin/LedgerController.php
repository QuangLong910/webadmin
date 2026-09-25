<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThueXeApi;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function index(Request $request, ThueXeApi $api)
    {
        $bookingId = (int) $request->query('bookingId');

        return view('ledger.index', [
            'bookingId' => $bookingId ?: null,
            'ledger' => $bookingId ? $api->ledger($bookingId) : null,
        ]);
    }
}

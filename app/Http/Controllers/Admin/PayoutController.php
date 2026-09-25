<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThueXeApi;
use App\Support\Fmt;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index(Request $request, ThueXeApi $api)
    {
        $status = $request->query('status', 'CHO');
        $payouts = $api->payouts($status ?: null);

        return view('payouts.index', [
            'payouts' => $payouts,
            'status' => $status,
            'total' => array_sum(array_column($payouts, 'amount')),
        ]);
    }

    /** F5 · Ghi nhận đã chuyển khoản tay. Bắt buộc mã giao dịch trên sao kê để sau này đối soát. */
    public function paid(Request $request, ThueXeApi $api, int $id)
    {
        $data = $request->validate([
            'transfer_ref' => ['required', 'string', 'max:100'],
            'no_bank' => ['nullable', 'boolean'],
            'bank_confirmed' => ['nullable', 'boolean'],
        ], [
            'transfer_ref.required' => 'Phải dán mã giao dịch trên sao kê — không có thì sau này đối soát không tra được.',
        ]);

        // Lệnh hoàn cọc chưa có số tài khoản khách: bắt người vận hành xác nhận đã hỏi khách.
        if (! empty($data['no_bank']) && empty($data['bank_confirmed'])) {
            return back()->withInput()->with('error', 'Lệnh #'.$id.' chưa có số tài khoản người nhận. Hãy gọi hỏi số tài khoản, chuyển khoản, rồi tích ô xác nhận.');
        }

        $res = $api->markPayoutPaid($id, trim($data['transfer_ref']), $request->input('idem'));

        return back()->with('success', "Lệnh chi #{$id}: ".Fmt::statusLabel($res['status'] ?? null)
            .' · mã GD '.($res['transferRef'] ?? $data['transfer_ref']).'. Đơn chỉ sang Hoàn tất khi mọi lệnh chi của đơn đã chi.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThueXeApi;
use App\Support\Fmt;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public const STATUSES = [
        'CHO_THANH_TOAN', 'DA_XAC_NHAN', 'DANG_GIAO', 'DANG_THUE',
        'CHO_QUYET_TOAN', 'CHO_CHI_TRA', 'HOAN_TAT', 'TRANH_CHAP', 'HET_HAN', 'DA_HUY',
    ];

    public function index(Request $request, ThueXeApi $api)
    {
        $status = $request->query('status', '');

        return view('bookings.index', [
            'bookings' => $api->bookings($status ?: null),
            'status' => $status,
        ]);
    }

    public function find(Request $request, ThueXeApi $api)
    {
        $q = trim((string) $request->query('q'));

        if (ctype_digit($q)) {
            return redirect()->route('bookings.show', (int) $q);
        }

        // Tìm theo mã đơn (KNM...) trong danh sách đang lấy được.
        foreach ($api->bookings() as $b) {
            if (strcasecmp($b['code'] ?? '', $q) === 0) {
                return redirect()->route('bookings.show', $b['id']);
            }
        }

        return back()->with('error', "Không tìm thấy đơn \"{$q}\".");
    }

    public function show(ThueXeApi $api, int $id)
    {
        $detail = $api->booking($id);
        $booking = $detail['booking'] ?? [];

        $payouts = array_values(array_filter($api->payouts(), fn ($p) => ($p['bookingId'] ?? null) === $id));

        return view('bookings.show', [
            'booking' => $booking,
            'payment' => $detail['payment'] ?? null,
            'handovers' => $detail['handovers'] ?? [],
            'charges' => $detail['charges'] ?? [],
            'payouts' => $payouts,
            'ledger' => $api->ledger($id),
        ]);
    }

    /** C2/C5 · Đối chiếu sao kê rồi xác nhận tiền vào ví treo. */
    public function confirmPayment(Request $request, ThueXeApi $api, int $id)
    {
        $data = $request->validate([
            'payment_id' => ['required', 'integer'],
            'received_amount' => ['required', 'integer', 'min:1'],
            'bank_note' => ['nullable', 'string', 'max:500'],
        ], [], ['received_amount' => 'số tiền thực nhận']);

        $res = $api->confirmPayment((int) $data['payment_id'], (int) $data['received_amount'], $data['bank_note'] ?? null, $request->input('idem'));

        if (! ($res['khopSoTien'] ?? false)) {
            $lech = (int) ($res['lechSoTien'] ?? 0);

            return back()->with('warning', 'Số tiền KHÔNG khớp phiếu thu: lệch '.Fmt::money($lech)
                .($lech < 0 ? ' (khách chuyển thiếu — gọi khách chuyển bù).' : ' (khách chuyển dư — cần hoàn phần dư).')
                .' Đơn giữ nguyên trạng thái '.Fmt::statusLabel($res['booking']['status'] ?? null).'.');
        }

        return back()->with('success', 'Đã xác nhận tiền vào ví treo. Đơn sang '
            .Fmt::statusLabel($res['booking']['status'] ?? null).', đã mở địa chỉ giao xe và số điện thoại cho hai bên.');
    }

    /** F2 · Chốt phí. Không gửi charges thì backend tự tính từ hai biên bản. */
    public function settle(Request $request, ThueXeApi $api, int $id)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['auto', 'manual'])],
            'charges' => ['array'],
            'charges.*.type' => ['nullable', Rule::in(array_keys(Fmt::CHARGE_TYPES))],
            'charges.*.amount' => ['nullable', 'integer', 'min:1'],
            'charges.*.note' => ['nullable', 'string', 'max:300'],
        ], [], ['charges.*.amount' => 'số tiền phí', 'charges.*.type' => 'loại phí']);

        $charges = null;
        if ($data['mode'] === 'manual') {
            $charges = [];
            foreach ($data['charges'] ?? [] as $row) {
                if (empty($row['type']) && empty($row['amount'])) {
                    continue;
                }
                if (empty($row['type']) || empty($row['amount'])) {
                    return back()->withInput()->with('error', 'Mỗi dòng phí phải có cả loại phí và số tiền.');
                }
                $charges[] = ['type' => $row['type'], 'amount' => (int) $row['amount'], 'note' => $row['note'] ?? null];
            }
        }

        $res = $api->settle($id, $charges, $request->input('idem'));

        $n = count($res['payouts'] ?? []);

        return back()->with('success', 'Đã chốt quyết toán. Đơn sang '.Fmt::statusLabel($res['booking']['status'] ?? null)
            .", sinh {$n} lệnh chi — xử lý ở mục Chi trả.");
    }
}

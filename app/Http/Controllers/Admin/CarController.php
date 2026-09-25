<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThueXeApi;
use App\Support\Fmt;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /** Đặc tả §5.2 bước 6: từ chối phải chọn lý do từ danh sách. */
    public const REJECT_REASONS = [
        'Tên trên đăng ký xe khác CCCD chủ xe và không có giấy uỷ quyền',
        'Thiếu hoặc mờ ảnh đăng ký xe',
        'Đăng kiểm hoặc bảo hiểm đã hết hạn / sắp hết hạn trong 7 ngày',
        'Ảnh xe không đủ khung mẫu hoặc không phải xe thật',
        'Thông tin xe (biển số, hãng, đời) không khớp giấy tờ',
    ];

    public function index(Request $request, ThueXeApi $api)
    {
        $status = $request->query('status', 'CHO_DUYET');
        $cars = array_map([self::class, 'withChecks'], $api->cars($status ?: null));

        return view('cars.index', [
            'cars' => $cars,
            'status' => $status,
            'reasons' => self::REJECT_REASONS,
        ]);
    }

    public function review(Request $request, ThueXeApi $api, int $id)
    {
        $data = $request->validate([
            'approved' => ['required', 'boolean'],
            'reason_preset' => ['nullable', 'string'],
            'reason_note' => ['nullable', 'string', 'max:500'],
        ]);

        $approved = (bool) $data['approved'];
        $reason = $approved ? null : DocumentController::composeReason($data['reason_preset'] ?? null, $data['reason_note'] ?? null);

        if (! $approved && $reason === null) {
            return back()->withInput()->with('error', 'Từ chối xe thì phải chọn hoặc ghi lý do.')->with('open_reject', $id);
        }

        $res = $api->reviewCar($id, $approved, $reason, $request->input('idem'));

        $msg = "Xe #{$id}: ".Fmt::statusLabel($res['status'] ?? null).'.';
        if (($res['status'] ?? null) === 'DANG_BAN') {
            $msg .= ' Xe đã hiện trong kết quả tìm kiếm của khách.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Gắn sẵn các cờ để người vận hành không bỏ sót (đặc tả §5.2, §5.2b):
     * có giấy uỷ quyền không, thiếu loại giấy nào, giấy nào hết hạn / sắp hết hạn trong 7 ngày.
     */
    private static function withChecks(array $car): array
    {
        $docs = $car['documents'] ?? [];
        $types = array_column($docs, 'type');
        $today = Carbon::today();

        $car['hasPowerOfAttorney'] = in_array('UY_QUYEN', $types, true);
        $car['missingDocs'] = array_values(array_diff(['DANG_KY', 'DANG_KIEM'], $types));
        $car['expiryWarnings'] = [];

        foreach ($docs as $doc) {
            if (empty($doc['expiryDate'])) {
                continue;
            }
            $days = $today->diffInDays(Carbon::parse($doc['expiryDate']), false);
            if ($days < 0) {
                $car['expiryWarnings'][] = Fmt::label($doc['type']).' đã hết hạn';
            } elseif ($days <= 7) {
                $car['expiryWarnings'][] = Fmt::label($doc['type'])." hết hạn sau {$days} ngày";
            }
        }

        return $car;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThueXeApi;
use App\Support\Fmt;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /** Lý do từ chối có sẵn — đặc tả §5.1: kiểm duyệt viên chọn từ danh sách. */
    public const REJECT_REASONS = [
        'Ảnh CCCD mờ, loá hoặc thiếu một mặt',
        'Ảnh chụp lại từ màn hình, không phải giấy tờ gốc',
        'Họ tên trên CCCD không khớp giấy phép lái xe',
        'Giấy phép lái xe hết hạn hoặc không đúng hạng',
        'Ảnh khuôn mặt không khớp CCCD',
    ];

    public function index(Request $request, ThueXeApi $api)
    {
        $status = $request->query('status', 'CHO_DUYET');

        return view('documents.index', [
            'documents' => $api->documents($status ?: null),
            'status' => $status,
            'reasons' => self::REJECT_REASONS,
        ]);
    }

    public function show(ThueXeApi $api, int $id)
    {
        $doc = collect($api->documents(null))->firstWhere('id', $id);
        abort_if($doc === null, 404, 'Không tìm thấy giấy tờ.');

        return view('documents.show', ['doc' => $doc, 'reasons' => self::REJECT_REASONS]);
    }

    /** Ảnh giấy tờ đi qua đây (có đăng nhập) thay vì để trình duyệt gọi thẳng backend. */
    public function image(ThueXeApi $api, int $id, string $side)
    {
        abort_unless(in_array($side, ['front', 'back', 'selfie'], true), 404);
        $img = $api->documentImage($id, $side);
        abort_if($img === null, 404, 'Không có ảnh.');

        return response($img[0], 200, [
            'Content-Type' => $img[1],
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex',
            'Content-Disposition' => 'inline',
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
        $reason = $approved ? null : self::composeReason($data['reason_preset'] ?? null, $data['reason_note'] ?? null);

        // A2 · Từ chối bắt buộc có lý do để khách biết sửa gì mà nộp lại.
        if (! $approved && $reason === null) {
            return back()->withInput()->with('error', 'Từ chối thì phải chọn hoặc ghi lý do.')->with('open_reject', $id);
        }

        $res = $api->reviewDocument($id, $approved, $reason, $request->input('idem'));

        // Xong một hồ sơ thì về hàng chờ để duyệt tiếp hồ sơ khác.
        return redirect()->route('documents.index')->with('success', "Giấy tờ #{$id}: ".Fmt::statusLabel($res['status'] ?? null).'.');
    }

    /** Ghép lý do chọn sẵn và ghi chú thêm thành một câu gửi backend. */
    public static function composeReason(?string $preset, ?string $note): ?string
    {
        $parts = array_filter([trim((string) $preset), trim((string) $note)], fn ($s) => $s !== '');

        return $parts ? implode(' — ', $parts) : null;
    }
}

<?php

namespace App\Support;

use App\Services\ThueXeApi;
use Carbon\Carbon;

/**
 * Định dạng hiển thị dùng chung cho các view quản trị.
 */
class Fmt
{
    /** Nhãn tiếng Việt và màu Bootstrap cho các mã trạng thái backend trả về. */
    private const STATUS = [
        // Giấy tờ, xe
        'CHO_DUYET' => ['Chờ duyệt', 'warning'],
        'DAT' => ['Đạt', 'success'],
        'KHONG_DAT' => ['Không đạt', 'danger'],
        'TU_CHOI' => ['Từ chối', 'danger'],
        'DANG_BAN' => ['Đang cho thuê', 'success'],
        'TAM_AN' => ['Tạm ẩn', 'secondary'],
        'DA_GO' => ['Đã gỡ', 'dark'],
        // Đơn thuê
        'NHAP' => ['Nháp', 'secondary'],
        'CHO_THANH_TOAN' => ['Chờ khách trả tiền', 'warning'],
        'DA_XAC_NHAN' => ['Đã xác nhận', 'primary'],
        'DANG_GIAO' => ['Đang giao', 'info'],
        'DANG_THUE' => ['Đang thuê', 'info'],
        'CHO_QUYET_TOAN' => ['Chờ quyết toán', 'warning'],
        'CHO_CHI_TRA' => ['Chờ chi trả', 'warning'],
        'HOAN_TAT' => ['Hoàn tất', 'success'],
        'HET_HAN' => ['Hết hạn', 'secondary'],
        'DA_HUY' => ['Đã huỷ', 'secondary'],
        'TRANH_CHAP' => ['Tranh chấp', 'danger'],
        // Phiếu thu, lệnh chi
        'CHO' => ['Chờ', 'warning'],
        'DA_NHAN' => ['Đã nhận', 'success'],
        'DA_CHI' => ['Đã chi', 'success'],
        'THAT_BAI' => ['Thất bại', 'danger'],
    ];

    public const DOC_TYPES = [
        'DANG_KY' => 'Đăng ký xe',
        'DANG_KIEM' => 'Đăng kiểm',
        'BAO_HIEM' => 'Bảo hiểm TNDS',
        'UY_QUYEN' => 'Giấy uỷ quyền',
    ];

    public const CHARGE_TYPES = [
        'QUA_GIO' => 'Quá giờ',
        'QUA_KM' => 'Quá km',
        'NHIEN_LIEU' => 'Nhiên liệu thiếu',
    ];

    public const ACCOUNTS = [
        'KHACH' => 'Khách',
        'CHU_XE' => 'Chủ xe',
        'SAN' => 'Sàn',
        'VI_TREO' => 'Ví treo',
    ];

    private const MISC = [
        'SO_TU_DONG' => 'Số tự động',
        'SO_SAN' => 'Số sàn',
        'XANG' => 'Xăng',
        'DAU' => 'Dầu',
        'DIEN' => 'Điện',
        'HYBRID' => 'Hybrid',
        'GIAO' => 'Giao xe',
        'TRA' => 'Trả xe',
        'NO' => 'Nợ',
        'CO' => 'Có',
    ];

    /** URL tuyệt đối cho ảnh backend trả dạng "/files/...". */
    public static function file(?string $path): ?string
    {
        return app(ThueXeApi::class)->fileUrl($path);
    }

    public static function money(int|float|null $amount): string
    {
        if ($amount === null) {
            return '—';
        }

        return number_format((int) $amount, 0, ',', '.').'đ';
    }

    public static function date(?string $value): string
    {
        return $value ? Carbon::parse($value)->timezone(config('app.timezone'))->format('d/m/Y') : '—';
    }

    public static function datetime(?string $value): string
    {
        return $value ? Carbon::parse($value)->timezone(config('app.timezone'))->format('H:i d/m/Y') : '—';
    }

    /** "3 giờ trước" — dùng để thấy hàng chờ đã chờ bao lâu (cam kết 4h/24h trong đặc tả). */
    public static function ago(?string $value): string
    {
        return $value ? Carbon::parse($value)->locale('vi')->diffForHumans() : '—';
    }

    public static function statusLabel(?string $code): string
    {
        return self::STATUS[$code][0] ?? ($code ?? '—');
    }

    public static function statusColor(?string $code): string
    {
        return self::STATUS[$code][1] ?? 'secondary';
    }

    public static function label(?string $code): string
    {
        return self::DOC_TYPES[$code] ?? self::CHARGE_TYPES[$code] ?? self::ACCOUNTS[$code] ?? self::MISC[$code] ?? ($code ?? '—');
    }
}

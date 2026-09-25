<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lỗi trả về từ backend: 422 kèm { code, message } cho lỗi nghiệp vụ,
 * 401/403 khi token hết hạn hoặc thiếu quyền, 0 khi không kết nối được.
 */
class ApiException extends RuntimeException
{
    private const CODE_MESSAGES = [
        'WRONG_STATE' => 'Bản ghi đã đổi trạng thái — có thể người khác vừa xử lý. Tải lại trang để xem trạng thái mới.',
        'INVALID_INPUT' => 'Dữ liệu gửi lên chưa hợp lệ.',
        'NOT_FOUND' => 'Không tìm thấy bản ghi.',
    ];

    public function __construct(
        public readonly int $status,
        public readonly ?string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function isUnauthenticated(): bool
    {
        return $this->status === 401;
    }

    /** Câu hiển thị cho người vận hành: ưu tiên message của backend, kèm giải thích theo mã. */
    public function friendlyMessage(): string
    {
        $hint = self::CODE_MESSAGES[$this->errorCode] ?? null;
        $msg = trim($this->getMessage());

        if ($msg === '' || $msg === $hint) {
            return $hint ?? 'Có lỗi xảy ra khi gọi backend.';
        }

        return $hint && $this->errorCode === 'WRONG_STATE' ? "{$msg}. {$hint}" : $msg;
    }
}

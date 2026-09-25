<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Lớp gọi backend sàn thuê xe. Token của người vận hành nằm trong session.
 */
class ThueXeApi
{
    public const SESSION_TOKEN = 'api_token';
    public const SESSION_PROFILE = 'api_profile';

    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(config('services.thuexe_api.url'), config('services.thuexe_api.timeout'));
    }

    /** Đường dẫn ảnh backend trả dạng "/files/..." — ghép thành URL tuyệt đối để trình duyệt tải. */
    public function fileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return preg_match('#^https?://#', $path) ? $path : $this->baseUrl.'/'.ltrim($path, '/');
    }

    // ---- Xác thực ----

    public function login(string $phone, string $password): array
    {
        return $this->send('post', '/auth/login', ['phone' => $phone, 'password' => $password], withToken: false);
    }

    // ---- Giấy tờ ----

    public function documents(?string $status = null): array
    {
        return $this->send('get', '/admin/documents', array_filter(['status' => $status]));
    }

    public function reviewDocument(int $id, bool $approved, ?string $reason, ?string $idempotencyKey = null): array
    {
        return $this->send('post', "/admin/documents/{$id}/review", ['approved' => $approved, 'reason' => $reason], $idempotencyKey);
    }

    // ---- Xe ----

    public function cars(?string $status = null): array
    {
        return $this->send('get', '/admin/cars', array_filter(['status' => $status]));
    }

    public function reviewCar(int $id, bool $approved, ?string $reason, ?string $idempotencyKey = null): array
    {
        return $this->send('post', "/admin/cars/{$id}/review", ['approved' => $approved, 'reason' => $reason], $idempotencyKey);
    }

    // ---- Đơn thuê ----

    /** Chưa có /admin/bookings nên tạm mượn đường của chủ xe (collection Postman cũng làm vậy). */
    public function bookings(?string $status = null): array
    {
        return $this->send('get', '/owner/bookings', array_filter(['status' => $status]));
    }

    /** Trả { booking, payment, handovers, charges }. */
    public function booking(int $id): array
    {
        return $this->send('get', "/bookings/{$id}");
    }

    public function confirmPayment(int $paymentId, int $receivedAmount, ?string $bankNote, ?string $idempotencyKey = null): array
    {
        return $this->send('post', "/admin/payments/{$paymentId}/confirm", [
            'receivedAmount' => $receivedAmount,
            'bankNote' => $bankNote,
        ], $idempotencyKey);
    }

    /** $charges = null → backend tự tính phí từ hai biên bản. */
    public function settle(int $bookingId, ?array $charges, ?string $idempotencyKey = null): array
    {
        $body = $charges === null ? (object) [] : ['charges' => $charges];

        return $this->send('post', "/admin/bookings/{$bookingId}/settle", $body, $idempotencyKey);
    }

    // ---- Chi trả & sổ cái ----

    public function payouts(?string $status = null): array
    {
        return $this->send('get', '/admin/payouts', array_filter(['status' => $status]));
    }

    public function markPayoutPaid(int $id, string $transferRef, ?string $idempotencyKey = null): array
    {
        return $this->send('post', "/admin/payouts/{$id}/paid", ['transferRef' => $transferRef], $idempotencyKey);
    }

    /** Trả { bookingId, entries, tongNo, tongCo, canBang }. */
    public function ledger(int $bookingId): array
    {
        return $this->send('get', '/admin/ledger', ['bookingId' => $bookingId]);
    }

    // ---- Nội bộ ----

    private function request(bool $withToken, ?string $idempotencyKey): PendingRequest
    {
        $req = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson();

        if ($withToken && ($token = session(self::SESSION_TOKEN))) {
            $req = $req->withToken($token);
        }

        // Đặc tả §7: lời gọi đổi tiền/trạng thái mang Idempotency-Key để bấm hai lần vẫn ra một lần.
        if ($idempotencyKey) {
            $req = $req->withHeaders(['Idempotency-Key' => $idempotencyKey]);
        }

        return $req;
    }

    private function send(string $method, string $path, array|object $data = [], ?string $idempotencyKey = null, bool $withToken = true): array
    {
        try {
            /** @var Response $res */
            $res = $this->request($withToken, $idempotencyKey)->{$method}($path, $data);
        } catch (ConnectionException $e) {
            throw new ApiException(0, 'CONNECTION', "Không kết nối được backend ({$this->baseUrl}). Kiểm tra Tailscale và máy chạy backend.");
        }

        $json = $res->json();
        [$status, $payload] = self::unwrap($res->status(), $json);

        if ($status >= 200 && $status < 300) {
            return is_array($payload) ? $payload : [];
        }

        $message = is_array($json) ? ($json['message'] ?? $json['title'] ?? '') : '';

        throw new ApiException(
            $status,
            is_array($json) ? ($json['code'] ?? null) : null,
            match ($status) {
                401 => 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại.',
                403 => 'Tài khoản không có quyền vận hành (VAN_HANH).',
                404 => $message ?: "Backend chưa có đường {$path}.",
                default => $message ?: "Backend trả lỗi HTTP {$status}.",
            },
        );
    }

    /**
     * Backend bọc kết quả trong vỏ { status, data } (lỗi: { status, code, message }), và mã
     * trong vỏ có thể khác mã HTTP. Trả [mã hiệu lực, dữ liệu bên trong].
     * Vẫn nhận kiểu cũ không có vỏ để không vỡ nếu backend đổi dần từng đường.
     */
    private static function unwrap(int $httpStatus, mixed $json): array
    {
        $isEnvelope = is_array($json)
            && isset($json['status']) && is_int($json['status'])
            && (array_key_exists('data', $json) || array_key_exists('code', $json));

        if (! $isEnvelope) {
            return [$httpStatus, $json];
        }

        // HTTP báo lỗi thì tin HTTP; HTTP 200 thì lấy mã trong vỏ.
        $status = $httpStatus >= 400 ? $httpStatus : $json['status'];

        return [$status, $json['data'] ?? null];
    }
}

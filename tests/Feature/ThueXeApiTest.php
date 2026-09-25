<?php

namespace Tests\Feature;

use App\Exceptions\ApiException;
use App\Services\ThueXeApi;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Lớp gọi API phải đọc được cả vỏ mới { status, data } / { status, code, message }
 * lẫn kiểu cũ không vỏ, kể cả khi HTTP 200 nhưng mã trong vỏ là lỗi.
 */
class ThueXeApiTest extends TestCase
{
    private ThueXeApi $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new ThueXeApi('http://be.test', 5);
    }

    public function test_login_bo_vo_data(): void
    {
        Http::fake(['be.test/auth/login' => Http::response(['status' => 200, 'data' => ['token' => 'abc', 'profile' => ['id' => 1]]])]);

        $this->assertSame('abc', $this->api->login('0364184928', 'test')['token']);
    }

    public function test_danh_sach_bo_vo_data(): void
    {
        Http::fake(['be.test/admin/cars*' => Http::response(['status' => 200, 'data' => [['id' => 144, 'status' => 'CHO_DUYET']]])]);

        $this->assertSame(144, $this->api->cars('CHO_DUYET')[0]['id']);
    }

    public function test_ket_qua_co_truong_status_chu_khong_nham_la_vo(): void
    {
        // Kiểu cũ: { id, status: "DAT" } — status là chuỗi nên không phải vỏ.
        Http::fake(['be.test/admin/documents/2/review' => Http::response(['id' => 2, 'status' => 'DAT'])]);

        $this->assertSame('DAT', $this->api->reviewDocument(2, true, null)['status']);
    }

    public function test_kieu_cu_khong_vo_van_doc_duoc(): void
    {
        Http::fake(['be.test/admin/payouts*' => Http::response([['id' => 27, 'status' => 'CHO']])]);

        $this->assertSame(27, $this->api->payouts()[0]['id']);
    }

    public function test_http_200_nhung_vo_bao_422_thi_nem_loi(): void
    {
        Http::fake(['be.test/admin/documents/1/review' => Http::response(['status' => 422, 'code' => 'WRONG_STATE', 'message' => 'Giấy tờ đã duyệt'], 200)]);

        $e = $this->catchApi(fn () => $this->api->reviewDocument(1, true, null));
        $this->assertSame(422, $e->status);
        $this->assertSame('WRONG_STATE', $e->errorCode);
        $this->assertStringContainsString('Giấy tờ đã duyệt', $e->friendlyMessage());
    }

    public function test_http_422_kem_vo(): void
    {
        Http::fake(['be.test/admin/ledger*' => Http::response(['status' => 422, 'code' => 'NOT_FOUND', 'message' => 'Không tìm thấy đơn'], 422)]);

        $e = $this->catchApi(fn () => $this->api->ledger(999999));
        $this->assertSame('NOT_FOUND', $e->errorCode);
    }

    public function test_vo_401_la_het_phien(): void
    {
        Http::fake(['be.test/bookings/5' => Http::response(['status' => 401, 'code' => 'UNAUTHORIZED', 'data' => null], 200)]);

        $this->assertTrue($this->catchApi(fn () => $this->api->booking(5))->isUnauthenticated());
    }

    public function test_xac_nhan_chuyen_thieu_doc_duoc_do_lech(): void
    {
        Http::fake(['be.test/admin/payments/9/confirm' => Http::response(['status' => 200, 'data' => [
            'khopSoTien' => false, 'lechSoTien' => -3860000, 'booking' => ['status' => 'CHO_THANH_TOAN'],
        ]])]);

        $res = $this->api->confirmPayment(9, 1000000, 'chuyen thieu');
        $this->assertFalse($res['khopSoTien']);
        $this->assertSame(-3860000, $res['lechSoTien']);
    }

    public function test_quyet_toan_tu_tinh_khong_gui_charges(): void
    {
        Http::fake(['be.test/admin/bookings/7/settle' => Http::response(['status' => 200, 'data' => ['booking' => ['status' => 'CHO_CHI_TRA'], 'payouts' => [[], []]]])]);

        $this->api->settle(7, null);

        Http::assertSent(fn ($req) => ! array_key_exists('charges', $req->data()));
    }

    private function catchApi(callable $fn): ApiException
    {
        try {
            $fn();
        } catch (ApiException $e) {
            return $e;
        }
        $this->fail('Mong đợi ApiException nhưng không có.');
    }
}

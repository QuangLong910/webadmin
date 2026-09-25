@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    @php
        $cards = [
            ['documents.index', ['status' => 'CHO_DUYET'], 'bi-person-vcard', 'Giấy tờ chờ duyệt', $docCount, 'Cam kết trả lời trong 4 giờ hành chính'],
            ['cars.index', ['status' => 'CHO_DUYET'], 'bi-car-front', 'Xe chờ duyệt', $carCount, 'Duyệt trong 24 giờ, so tên đăng ký với CCCD'],
            ['bookings.index', ['status' => 'CHO_THANH_TOAN'], 'bi-qr-code', 'Đơn chờ khách trả tiền', $unpaidCount, 'Đối chiếu sao kê rồi xác nhận'],
            ['bookings.index', ['status' => 'CHO_QUYET_TOAN'], 'bi-calculator', 'Đơn chờ quyết toán', $settleCount, 'Đã đủ biên bản giao và trả'],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($cards as [$route, $params, $icon, $label, $count, $hint])
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route($route, $params) }}" class="card h-100 text-decoration-none {{ $count ? 'card-queue' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary">{{ $label }}</span>
                            <i class="bi {{ $icon }} fs-4 text-secondary"></i>
                        </div>
                        <div class="display-6 fw-semibold text-body">{{ $count }}</div>
                        <div class="small text-secondary">{{ $hint }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card {{ $payoutCount ? 'border-warning' : '' }}">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <i class="bi bi-cash-stack fs-2 text-warning"></i>
            <div class="flex-grow-1">
                <div class="fw-semibold">Việc mỗi tối: {{ $payoutCount }} lệnh chi chờ chuyển khoản · tổng <span class="money">{{ Fmt::money($payoutTotal) }}</span></div>
                <div class="small text-secondary">Chuyển tay trong 24 giờ, dán mã giao dịch, rồi đối soát sao kê với sổ cái.</div>
                @if ($payoutNoBank)
                    <div class="small text-danger mt-1"><i class="bi bi-exclamation-triangle"></i> {{ $payoutNoBank }} lệnh chưa có số tài khoản người nhận — phải gọi hỏi trước khi chuyển.</div>
                @endif
            </div>
            <a href="{{ route('payouts.index') }}" class="btn btn-warning">Mở danh sách chi trả</a>
        </div>
    </div>

    <p class="small text-secondary mt-4 mb-0">
        <i class="bi bi-info-circle"></i> Số đơn lấy tạm qua <code>/owner/bookings</code> vì backend chưa có <code>/admin/bookings</code> — chỉ thấy đơn của xe thuộc tài khoản đang đăng nhập.
    </p>
@endsection

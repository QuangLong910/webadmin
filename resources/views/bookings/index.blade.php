@extends('layouts.admin')

@section('title', 'Đơn thuê & tiền vào')

@section('content')
    @include('partials.status-tabs', [
        'route' => 'bookings.index',
        'current' => $status,
        'tabs' => [
            '' => 'Tất cả',
            'CHO_THANH_TOAN' => 'Chờ khách trả tiền',
            'DA_XAC_NHAN' => 'Đã xác nhận',
            'DANG_THUE' => 'Đang thuê',
            'CHO_QUYET_TOAN' => 'Chờ quyết toán',
            'CHO_CHI_TRA' => 'Chờ chi trả',
            'HOAN_TAT' => 'Hoàn tất',
            'TRANH_CHAP' => 'Tranh chấp',
        ],
    ])

    <div class="alert alert-info small py-2">
        <i class="bi bi-info-circle"></i> Backend chưa có <code>/admin/bookings</code> nên danh sách tạm lấy qua <code>/owner/bookings</code>
        (chỉ đơn của xe thuộc tài khoản đang đăng nhập). Đơn khác: gõ mã/id vào ô tìm ở góc trên.
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Mã đơn</th>
                    <th>Xe</th>
                    <th>Khách</th>
                    <th>Thời gian</th>
                    <th class="text-end">Tiền thuê</th>
                    <th class="text-end">Cọc</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($bookings as $b)
                    <tr>
                        <td><a href="{{ route('bookings.show', $b['id']) }}" class="fw-semibold font-monospace">{{ $b['code'] }}</a><div class="small text-secondary">#{{ $b['id'] }}</div></td>
                        <td>{{ $b['car']['brand'] ?? '' }} {{ $b['car']['model'] ?? '' }}<div class="small text-secondary">{{ $b['car']['plate'] ?? '' }}</div></td>
                        <td>{{ $b['renter']['fullName'] ?? '—' }}<div class="small text-secondary">{{ $b['renter']['phone'] ?? '' }}</div></td>
                        <td class="small">{{ Fmt::date($b['startDate']) }} → {{ Fmt::date($b['endDate']) }}<div class="text-secondary">{{ $b['days'] }} ngày</div></td>
                        <td class="text-end money">{{ Fmt::money($b['rentTotal']) }}</td>
                        <td class="text-end money">{{ Fmt::money($b['deposit']) }}</td>
                        <td><x-status :code="$b['status']" /></td>
                        <td class="text-end">
                            @php
                                $cta = match ($b['status']) {
                                    'CHO_THANH_TOAN' => ['Xác nhận tiền', 'btn-warning'],
                                    'CHO_QUYET_TOAN' => ['Quyết toán', 'btn-warning'],
                                    default => ['Chi tiết', 'btn-outline-secondary'],
                                };
                            @endphp
                            <a href="{{ route('bookings.show', $b['id']) }}" class="btn btn-sm {{ $cta[1] }}">{{ $cta[0] }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-secondary py-4">Không có đơn nào.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

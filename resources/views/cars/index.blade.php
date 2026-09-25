@extends('layouts.admin')

@section('title', 'Duyệt xe')

@section('content')
    @include('partials.status-tabs', [
        'route' => 'cars.index',
        'current' => $status,
        'tabs' => ['CHO_DUYET' => 'Chờ duyệt', 'DANG_BAN' => 'Đang cho thuê', '' => 'Tất cả'],
    ])

    @if ($status === 'CHO_DUYET' && count($cars))
        <div class="alert alert-warning small">
            <i class="bi bi-shield-exclamation"></i>
            <strong>Bước hay bị bỏ sót:</strong> đối chiếu tên trên <em>đăng ký xe</em> với CCCD chủ xe.
            Khác tên mà không có <em>giấy uỷ quyền</em> thì từ chối, không có ngoại lệ.
        </div>
    @endif

    @forelse ($cars as $car)
        @php
            $docs = collect($car['documents'] ?? []);
            $dangKy = $docs->firstWhere('type', 'DANG_KY');
            $uyQuyen = $docs->firstWhere('type', 'UY_QUYEN');
            $others = $docs->reject(fn ($d) => in_array($d['type'], ['DANG_KY', 'UY_QUYEN']));
        @endphp
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <strong>#{{ $car['id'] }} · {{ $car['brand'] }} {{ $car['model'] }} {{ $car['year'] }}</strong>
                <span class="badge text-bg-dark">{{ $car['plate'] }}</span>
                <span class="text-secondary small">gửi {{ Fmt::ago($car['createdAt'] ?? null) }}</span>
                <x-status :code="$car['status']" class="ms-auto" />
            </div>
            <div class="card-body">
                {{-- Cờ kiểm tra tự động --}}
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if ($car['hasPowerOfAttorney'])
                        <span class="badge text-bg-info"><i class="bi bi-file-earmark-text"></i> Có giấy uỷ quyền</span>
                    @else
                        <span class="badge text-bg-light border">Không có giấy uỷ quyền — tên đăng ký phải trùng CCCD</span>
                    @endif
                    @foreach ($car['missingDocs'] as $t)
                        <span class="badge text-bg-danger">Thiếu {{ Fmt::label($t) }}</span>
                    @endforeach
                    @foreach ($car['expiryWarnings'] as $w)
                        <span class="badge text-bg-danger">{{ $w }}</span>
                    @endforeach
                </div>

                {{-- So bằng mắt: CCCD chủ xe cạnh đăng ký xe --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="small text-secondary">Chủ xe (theo CCCD đã duyệt)</div>
                            <div class="fs-5 fw-semibold">{{ $car['owner']['fullName'] ?? '—' }}</div>
                            <div>CCCD: <span class="font-monospace">{{ $car['owner']['cccdNo'] ?? 'chưa có' }}</span></div>
                            <div class="text-secondary small"><i class="bi bi-telephone"></i> {{ $car['owner']['phone'] ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-secondary mb-1">Đăng ký xe — đọc tên chủ sở hữu</div>
                        @if ($dangKy)
                            <img class="doc-img" src="{{ Fmt::file($dangKy['url']) }}" alt="Đăng ký xe">
                        @else
                            <div class="text-danger">Chưa nộp đăng ký xe</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        @if ($uyQuyen)
                            <div class="small text-secondary mb-1">Giấy uỷ quyền</div>
                            <img class="doc-img" src="{{ Fmt::file($uyQuyen['url']) }}" alt="Giấy uỷ quyền">
                        @else
                            <div class="small text-secondary mb-1">Ảnh xe</div>
                            @php $photo = collect($car['photos'] ?? [])->sortBy('sortOrder')->first(); @endphp
                            @if ($photo)<img class="doc-img" src="{{ Fmt::file($photo['url']) }}" alt="Ảnh xe">@endif
                        @endif
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-lg-6">
                        <table class="table table-sm mb-0">
                            <tr><th class="text-secondary fw-normal">Số chỗ · hộp số · nhiên liệu</th><td>{{ $car['seats'] }} chỗ · {{ Fmt::label($car['transmission']) }} · {{ Fmt::label($car['fuel']) }}</td></tr>
                            <tr><th class="text-secondary fw-normal">ODO</th><td>{{ number_format($car['odo'] ?? 0, 0, ',', '.') }} km</td></tr>
                            <tr><th class="text-secondary fw-normal">Giá ngày · cọc</th><td class="money">{{ Fmt::money($car['pricePerDay']) }} · {{ Fmt::money($car['deposit']) }}</td></tr>
                            <tr><th class="text-secondary fw-normal">Giới hạn</th><td>{{ $car['maxKmDay'] }} km/ngày</td></tr>
                            <tr><th class="text-secondary fw-normal">Nhận xe</th><td>{{ $car['pickupAddress'] }}</td></tr>
                            @if (!empty($car['rejectReason']))
                                <tr><th class="text-secondary fw-normal">Lý do từ chối</th><td class="text-danger">{{ $car['rejectReason'] }}</td></tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-lg-6">
                        <div class="small text-secondary mb-1">Giấy tờ khác · ảnh xe ({{ count($car['photos'] ?? []) }})</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($others as $d)
                                <div style="width: 140px">
                                    <img class="doc-img" style="height: 100px" src="{{ Fmt::file($d['url']) }}" alt="{{ Fmt::label($d['type']) }}">
                                    <div class="small">{{ Fmt::label($d['type']) }}@if ($d['expiryDate']) · HH {{ Fmt::date($d['expiryDate']) }}@endif</div>
                                </div>
                            @endforeach
                            @foreach (collect($car['photos'] ?? [])->sortBy('sortOrder') as $p)
                                <img class="doc-img" style="width: 140px; height: 100px" src="{{ Fmt::file($p['url']) }}" alt="Ảnh xe">
                            @endforeach
                        </div>
                        @if (!empty($car['description']))<p class="small mt-2 mb-0">{{ $car['description'] }}</p>@endif
                    </div>
                </div>

                @if ($car['status'] === 'CHO_DUYET')
                    <hr>
                    @include('partials.review-actions', [
                        'action' => route('cars.review', $car['id']),
                        'id' => $car['id'],
                        'reasons' => $reasons,
                        'approveText' => 'Duyệt xe — cho lên sàn',
                        'approveConfirm' => 'Đã so tên trên đăng ký xe '.$car['plate'].' với CCCD chủ xe? Duyệt thì xe hiện ngay cho khách tìm.',
                    ])
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-light border"><i class="bi bi-check2-circle text-success"></i> Không có xe nào trong mục này.</div>
    @endforelse
@endsection

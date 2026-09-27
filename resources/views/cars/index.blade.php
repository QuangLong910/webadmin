@extends('layouts.admin')

@section('title', 'Duyệt xe')

@section('content')
    @include('partials.status-tabs', [
        'route' => 'cars.index',
        'current' => $status,
        'tabs' => ['CHO_DUYET' => 'Chờ duyệt', 'DANG_BAN' => 'Đang cho thuê', '' => 'Tất cả'],
    ])

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Xe</th>
                    <th>Chủ xe</th>
                    <th>Cần chú ý</th>
                    <th>Gửi</th>
                    <th>Trạng thái</th>
                    <th class="text-end"></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($cars as $car)
                    @php $canhBao = count($car['missingDocs'] ?? []) + count($car['expiryWarnings'] ?? []); @endphp
                    <tr>
                        <td class="text-secondary">#{{ $car['id'] }}</td>
                        <td>
                            <div class="fw-semibold">{{ $car['brand'] }} {{ $car['model'] }} {{ $car['year'] }}</div>
                            <span class="badge text-bg-dark">{{ $car['plate'] }}</span>
                        </td>
                        <td>{{ $car['owner']['fullName'] ?? '—' }}</td>
                        <td>
                            @if ($canhBao)
                                <span class="badge text-bg-danger">{{ $canhBao }} cảnh báo giấy tờ</span>
                            @else
                                <span class="text-secondary small">—</span>
                            @endif
                            @if ($car['hasPowerOfAttorney'])
                                <span class="badge text-bg-info">Có uỷ quyền</span>
                            @endif
                        </td>
                        <td class="text-secondary small">{{ Fmt::ago($car['createdAt'] ?? null) }}</td>
                        <td><x-status :code="$car['status']" /></td>
                        <td class="text-end">
                            <a class="btn btn-sm {{ $car['status'] === 'CHO_DUYET' ? 'btn-primary' : 'btn-outline-secondary' }}"
                               href="{{ route('cars.show', $car['id']) }}">
                                {{ $car['status'] === 'CHO_DUYET' ? 'Xem và duyệt' : 'Chi tiết' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4"><i class="bi bi-check2-circle text-success"></i> Không có xe nào trong mục này.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

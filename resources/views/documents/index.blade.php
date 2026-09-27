@extends('layouts.admin')

@section('title', 'Duyệt giấy tờ khách')

@section('content')
    @include('partials.status-tabs', [
        'route' => 'documents.index',
        'current' => $status,
        'tabs' => ['CHO_DUYET' => 'Chờ duyệt', 'DAT' => 'Đã đạt', '' => 'Tất cả'],
    ])

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Người nộp</th>
                    <th>GPLX hết hạn</th>
                    <th>Trạng thái</th>
                    <th class="text-end"></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($documents as $doc)
                    <tr>
                        <td class="text-secondary">#{{ $doc['id'] }}</td>
                        <td>
                            <div class="fw-semibold">{{ $doc['user']['fullName'] ?? '—' }}</div>
                            <div class="small text-secondary"><i class="bi bi-telephone"></i> {{ $doc['user']['phone'] ?? '—' }}</div>
                        </td>
                        <td>
                            {{ Fmt::date($doc['gplxExpiry'] ?? null) }}
                            @if (!empty($doc['gplxExpiry']) && \Carbon\Carbon::parse($doc['gplxExpiry'])->isPast())
                                <span class="badge text-bg-danger">Đã hết hạn</span>
                            @endif
                        </td>
                        <td><x-status :code="$doc['status']" /></td>
                        <td class="text-end">
                            <a class="btn btn-sm {{ $doc['status'] === 'CHO_DUYET' ? 'btn-primary' : 'btn-outline-secondary' }}"
                               href="{{ route('documents.show', $doc['id']) }}">
                                {{ $doc['status'] === 'CHO_DUYET' ? 'Xem và duyệt' : 'Chi tiết' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4"><i class="bi bi-check2-circle text-success"></i> Không có giấy tờ nào trong mục này.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

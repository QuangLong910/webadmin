@extends('layouts.admin')

@section('title', 'Duyệt giấy tờ khách')

@section('content')
    @include('partials.status-tabs', [
        'route' => 'documents.index',
        'current' => $status,
        'tabs' => ['CHO_DUYET' => 'Chờ duyệt', 'DAT' => 'Đã đạt', '' => 'Tất cả'],
    ])

    @forelse ($documents as $doc)
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <strong>#{{ $doc['id'] }} · {{ $doc['user']['fullName'] ?? '—' }}</strong>
                <span class="text-secondary"><i class="bi bi-telephone"></i> {{ $doc['user']['phone'] ?? '—' }}</span>
                <x-status :code="$doc['status']" class="ms-auto" />
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="small text-secondary mb-1">CCCD mặt trước</div>
                        @if ($doc['frontUrl'])<img class="doc-img" src="{{ Fmt::file($doc['frontUrl']) }}" alt="CCCD mặt trước">@else<div class="text-danger">Chưa nộp</div>@endif
                    </div>
                    <div class="col-md-4">
                        <div class="small text-secondary mb-1">CCCD mặt sau</div>
                        @if ($doc['backUrl'])<img class="doc-img" src="{{ Fmt::file($doc['backUrl']) }}" alt="CCCD mặt sau">@else<div class="text-danger">Chưa nộp</div>@endif
                    </div>
                    <div class="col-md-4">
                        @if (!empty($doc['selfieUrl']))
                            <div class="small text-secondary mb-1">Ảnh khuôn mặt</div>
                            <img class="doc-img mb-2" src="{{ Fmt::file($doc['selfieUrl']) }}" alt="Ảnh khuôn mặt">
                        @endif
                        <table class="table table-sm mb-0">
                            <tr><th class="text-secondary fw-normal">Số CCCD</th><td>{{ $doc['cccdNo'] ?? '—' }}</td></tr>
                            <tr><th class="text-secondary fw-normal">Số GPLX</th><td>{{ $doc['gplxNo'] ?? '—' }}</td></tr>
                            <tr><th class="text-secondary fw-normal">Hạng</th><td>{{ $doc['gplxClass'] ?? '—' }}</td></tr>
                            <tr>
                                <th class="text-secondary fw-normal">GPLX hết hạn</th>
                                <td>
                                    {{ Fmt::date($doc['gplxExpiry'] ?? null) }}
                                    @if (!empty($doc['gplxExpiry']) && \Carbon\Carbon::parse($doc['gplxExpiry'])->isPast())
                                        <span class="badge text-bg-danger">Đã hết hạn</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($doc['reviewedAt'])
                                <tr><th class="text-secondary fw-normal">Duyệt lúc</th><td>{{ Fmt::datetime($doc['reviewedAt']) }}</td></tr>
                            @endif
                            @if ($doc['rejectReason'])
                                <tr><th class="text-secondary fw-normal">Lý do từ chối</th><td class="text-danger">{{ $doc['rejectReason'] }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if ($doc['status'] === 'CHO_DUYET')
                    <hr>
                    @include('partials.review-actions', [
                        'action' => route('documents.review', $doc['id']),
                        'id' => $doc['id'],
                        'reasons' => $reasons,
                        'approveText' => 'Duyệt đạt',
                        'approveConfirm' => 'Duyệt đạt giấy tờ của '.($doc['user']['fullName'] ?? '').'?',
                    ])
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-light border"><i class="bi bi-check2-circle text-success"></i> Không có giấy tờ nào trong mục này.</div>
    @endforelse
@endsection

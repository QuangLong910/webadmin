@extends('layouts.admin')

@section('title', 'Giấy tờ #'.$doc['id'])

@section('content')
    <a href="{{ route('documents.index', ['status' => $doc['status'] === 'CHO_DUYET' ? 'CHO_DUYET' : '']) }}" class="btn btn-link px-0 mb-2">
        <i class="bi bi-arrow-left"></i> Về danh sách giấy tờ
    </a>

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <strong>#{{ $doc['id'] }} · {{ $doc['user']['fullName'] ?? '—' }}</strong>
            <span class="text-secondary"><i class="bi bi-telephone"></i> {{ $doc['user']['phone'] ?? '—' }}</span>
            <x-status :code="$doc['status']" class="ms-auto" />
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="small text-secondary mb-2">Ảnh giấy tờ</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ([
                            ['front', 'CCCD mặt trước', $doc['frontUrl'] ?? null],
                            ['back', 'CCCD mặt sau', $doc['backUrl'] ?? null],
                            ['selfie', 'Ảnh khuôn mặt', $doc['selfieUrl'] ?? null],
                        ] as [$mat, $nhan, $url])
                            @if ($url)
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-doc-img="{{ route('documents.image', [$doc['id'], $mat]) }}" data-doc-title="{{ $nhan }}">
                                    <i class="bi bi-eye"></i> {{ $nhan }}
                                </button>
                            @else
                                <span class="btn btn-sm disabled text-danger border">{{ $nhan }}: chưa nộp</span>
                            @endif
                        @endforeach
                    </div>
                    <div class="form-text"><i class="bi bi-shield-lock"></i> Ảnh chỉ tải khi bấm xem và không được lưu lại trong trình duyệt.</div>
                </div>
                <div class="col-md-8">
                    <table class="table table-sm mb-0">
                        <tr><th class="text-secondary fw-normal">Số CCCD</th><td>@include('partials.so-che', ['so' => $doc['cccdNo'] ?? null])</td></tr>
                        <tr><th class="text-secondary fw-normal">Số GPLX</th><td>@include('partials.so-che', ['so' => $doc['gplxNo'] ?? null])</td></tr>
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
@endsection

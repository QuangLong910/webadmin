@extends('layouts.admin')

@section('title', 'Chi trả')

@section('content')
    @include('partials.status-tabs', [
        'route' => 'payouts.index',
        'current' => $status,
        'tabs' => ['CHO' => 'Chờ chuyển khoản', 'DA_CHI' => 'Đã chi', '' => 'Tất cả'],
    ])

    @if ($status === 'CHO')
        <div class="alert alert-light border small">
            <i class="bi bi-moon-stars"></i> <strong>Việc mỗi tối:</strong> chuyển khoản tay từng lệnh trong 24 giờ, dán <em>mã giao dịch trên sao kê</em>, rồi mở Sổ cái đối soát.
            Đơn chỉ sang <x-status code="HOAN_TAT" /> khi cả hai lệnh (chủ xe và khách) đã chi.
            <div class="mt-1">Tổng cần chuyển: <strong class="money">{{ Fmt::money($total) }}</strong> · {{ count($payouts) }} lệnh</div>
        </div>
    @endif

    @forelse ($payouts as $p)
        @php $noBank = ($p['bankAccount'] ?? null) === 'CHUA_CO' || empty($p['bankAccount']); @endphp
        <div class="card mb-2 {{ $noBank && $p['status'] === 'CHO' ? 'border-danger' : '' }}">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $p['payeeType'] === 'KHACH' ? 'text-bg-info' : 'text-bg-primary' }}">{{ $p['payeeType'] === 'KHACH' ? 'Hoàn cọc khách' : 'Trả chủ xe' }}</span>
                            <x-status :code="$p['status']" />
                        </div>
                        <div class="fw-semibold mt-1">{{ $p['payee']['fullName'] ?? '—' }}</div>
                        <div class="small text-secondary"><i class="bi bi-telephone"></i> {{ $p['payee']['phone'] ?? '—' }}</div>
                        <div class="small">Đơn <a href="{{ route('bookings.show', $p['bookingId']) }}" class="font-monospace">{{ $p['bookingCode'] ?? '#'.$p['bookingId'] }}</a> · lệnh #{{ $p['id'] }} · tạo {{ Fmt::ago($p['createdAt'] ?? null) }}</div>
                    </div>
                    <div class="col-lg-3">
                        @if ($noBank)
                            <div class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill"></i> Chưa có số tài khoản</div>
                            <div class="small text-danger">Gọi {{ $p['payee']['phone'] ?? 'người nhận' }} hỏi số tài khoản trước khi chuyển.</div>
                        @else
                            <div class="font-monospace fs-6">{{ $p['bankAccount'] }}</div>
                            <div class="small text-secondary">{{ $p['bankName'] }}</div>
                        @endif
                        <div class="fs-5 fw-semibold money mt-1">{{ Fmt::money($p['amount']) }}</div>
                    </div>
                    <div class="col-lg-5">
                        @if ($p['status'] === 'CHO')
                            <form method="post" action="{{ route('payouts.paid', $p['id']) }}"
                                  data-confirm="Ghi nhận đã chuyển {{ Fmt::money($p['amount']) }} cho {{ $p['payee']['fullName'] ?? '' }}?">
                                @csrf
                                <input type="hidden" name="idem" value="{{ Str::uuid() }}">
                                <input type="hidden" name="no_bank" value="{{ $noBank ? 1 : 0 }}">
                                <input type="hidden" name="payout_id" value="{{ $p['id'] }}">
                                @if ($noBank)
                                    <div class="form-check small mb-1">
                                        <input class="form-check-input" type="checkbox" name="bank_confirmed" value="1" id="bc-{{ $p['id'] }}" required>
                                        <label class="form-check-label" for="bc-{{ $p['id'] }}">Đã gọi hỏi và chuyển đúng tài khoản chính chủ của người nhận</label>
                                    </div>
                                @endif
                                <div class="input-group">
                                    <input class="form-control font-monospace" name="transfer_ref" placeholder="Mã giao dịch trên sao kê, VD FT2609…" required maxlength="100"
                                           value="{{ (int) old('payout_id') === $p['id'] ? old('transfer_ref') : '' }}">
                                    <button class="btn btn-success"><i class="bi bi-check2"></i> Đã chuyển</button>
                                </div>
                            </form>
                        @else
                            <div class="small">Mã GD: <span class="font-monospace">{{ $p['transferRef'] ?? '—' }}</span></div>
                            <div class="small text-secondary">Chi lúc {{ Fmt::datetime($p['paidAt'] ?? null) }}</div>
                            <a href="{{ route('ledger.index', ['bookingId' => $p['bookingId']]) }}" class="small">Xem sổ cái đơn →</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-light border"><i class="bi bi-check2-circle text-success"></i> Không có lệnh chi nào trong mục này.</div>
    @endforelse
@endsection

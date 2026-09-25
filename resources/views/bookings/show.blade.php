@extends('layouts.admin')

@section('title', 'Đơn '.($booking['code'] ?? '#'.$booking['id']))

@section('content')
    @php $status = $booking['status'] ?? null; @endphp

    <div class="row g-3">
        {{-- Thông tin đơn --}}
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center">
                    <strong>Thông tin đơn #{{ $booking['id'] }}</strong>
                    <x-status :code="$status" class="ms-auto fs-6" />
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 mb-3">
                        @if (!empty($booking['car']['photoUrl']))
                            <img class="doc-img" style="width: 120px; height: 80px" src="{{ Fmt::file($booking['car']['photoUrl']) }}" alt="Xe">
                        @endif
                        <div>
                            <div class="fw-semibold">{{ $booking['car']['brand'] ?? '' }} {{ $booking['car']['model'] ?? '' }}</div>
                            <span class="badge text-bg-dark">{{ $booking['car']['plate'] ?? '' }}</span>
                        </div>
                    </div>
                    <table class="table table-sm mb-0">
                        <tr><th class="text-secondary fw-normal">Khách thuê</th><td>{{ $booking['renter']['fullName'] ?? '—' }} · {{ $booking['renter']['phone'] ?? '' }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Giấy tờ khách</th><td><x-status :code="$booking['renter']['idDocumentStatus'] ?? null" /></td></tr>
                        <tr><th class="text-secondary fw-normal">Thời gian</th><td>{{ Fmt::date($booking['startDate'] ?? null) }} → {{ Fmt::date($booking['endDate'] ?? null) }} ({{ $booking['days'] ?? '?' }} ngày)</td></tr>
                        <tr><th class="text-secondary fw-normal">Giá ngày</th><td class="money">{{ Fmt::money($booking['pricePerDay'] ?? null) }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Tiền thuê</th><td class="money">{{ Fmt::money($booking['rentTotal'] ?? null) }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Tiền cọc</th><td class="money">{{ Fmt::money($booking['deposit'] ?? null) }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Hoa hồng sàn</th><td class="money">{{ Fmt::money($booking['commission'] ?? null) }}</td></tr>
                        <tr><th class="text-secondary fw-normal">Tạo lúc</th><td>{{ Fmt::datetime($booking['createdAt'] ?? null) }}</td></tr>
                        @if (!empty($booking['cancelReason']))
                            <tr><th class="text-secondary fw-normal">Lý do huỷ/hết hạn</th><td class="text-danger">{{ $booking['cancelReason'] }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Phiếu thu & xác nhận tiền vào --}}
        <div class="col-xl-7">
            <div class="card h-100 {{ $status === 'CHO_THANH_TOAN' ? 'border-warning' : '' }}">
                <div class="card-header"><strong><i class="bi bi-qr-code"></i> Tiền vào ví treo</strong></div>
                <div class="card-body">
                    @if (! $payment)
                        <p class="text-secondary mb-0">Đơn chưa có phiếu thu (phiếu thu sinh khi chủ xe nhận đơn).</p>
                    @else
                        <div class="row g-3">
                            <div class="col-md-5 text-center">
                                @if (!empty($payment['qrUrl']))
                                    <img src="{{ $payment['qrUrl'] }}" alt="Mã QR chuyển khoản" class="img-fluid border rounded" style="max-height: 220px">
                                @endif
                            </div>
                            <div class="col-md-7">
                                <table class="table table-sm">
                                    <tr><th class="text-secondary fw-normal">Phiếu thu</th><td>#{{ $payment['id'] }} <x-status :code="$payment['status'] ?? null" /></td></tr>
                                    <tr><th class="text-secondary fw-normal">Phải thu</th><td class="money fs-5 fw-semibold">{{ Fmt::money($payment['amount']) }}</td></tr>
                                    <tr><th class="text-secondary fw-normal">Nội dung CK</th><td class="font-monospace fw-semibold">{{ $payment['transferCode'] ?? '—' }}</td></tr>
                                    @if (($payment['receivedAmount'] ?? null) !== null)
                                        <tr><th class="text-secondary fw-normal">Thực nhận</th>
                                            <td class="money {{ $payment['receivedAmount'] == $payment['amount'] ? 'text-success' : 'text-danger fw-semibold' }}">
                                                {{ Fmt::money($payment['receivedAmount']) }}
                                                @if ($payment['receivedAmount'] != $payment['amount'])
                                                    (lệch {{ Fmt::money($payment['receivedAmount'] - $payment['amount']) }})
                                                @endif
                                            </td></tr>
                                    @endif
                                    @if (!empty($payment['confirmedAt']))
                                        <tr><th class="text-secondary fw-normal">Xác nhận lúc</th><td>{{ Fmt::datetime($payment['confirmedAt']) }}</td></tr>
                                    @endif
                                    @if (!empty($payment['bankNote']))
                                        <tr><th class="text-secondary fw-normal">Ghi chú sao kê</th><td>{{ $payment['bankNote'] }}</td></tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        @if ($status === 'CHO_THANH_TOAN' && ($payment['status'] ?? null) !== 'DA_NHAN')
                            <form method="post" action="{{ route('bookings.confirm-payment', $booking['id']) }}" class="border-top pt-3"
                                  data-confirm="Xác nhận đã đối chiếu sao kê và nhận tiền cho đơn {{ $booking['code'] }}?">
                                @csrf
                                <input type="hidden" name="idem" value="{{ Str::uuid() }}">
                                <input type="hidden" name="payment_id" value="{{ $payment['id'] }}">
                                <div class="small text-secondary mb-2">
                                    Mở sao kê tài khoản dự án, tìm giao dịch có nội dung <strong class="font-monospace">{{ $payment['transferCode'] }}</strong>, nhập <em>đúng số tiền thực nhận</em>.
                                    Lệch số tiền thì đơn không tự xác nhận — gọi khách chuyển bù.
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small">Số tiền thực nhận (đồng)</label>
                                        <input type="number" min="1" step="1" class="form-control" name="received_amount" value="{{ old('received_amount') }}" required placeholder="{{ $payment['amount'] }}">
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label small">Nội dung trên sao kê</label>
                                        <input class="form-control" name="bank_note" value="{{ old('bank_note') }}" maxlength="500" placeholder="VD: LE HOANG CUONG chuyen {{ $payment['transferCode'] }}">
                                    </div>
                                </div>
                                <button class="btn btn-warning mt-2"><i class="bi bi-check2-square"></i> Xác nhận tiền vào</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Biên bản giao / trả --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header"><strong><i class="bi bi-clipboard-check"></i> Biên bản giao / trả xe</strong></div>
                <div class="card-body">
                    @forelse ($handovers as $h)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong>{{ Fmt::label($h['kind'] ?? $h['type'] ?? null) }}</strong>
                                <span class="small text-secondary">{{ Fmt::datetime($h['createdAt'] ?? $h['signedAt'] ?? null) }}</span>
                            </div>
                            <div class="small">
                                ODO: <strong>{{ isset($h['odo']) ? number_format($h['odo'], 0, ',', '.').' km' : '—' }}</strong>
                                · Nhiên liệu: <strong>{{ $h['fuelLevel'] ?? '—' }}{{ isset($h['fuelLevel']) ? '/8' : '' }}</strong>
                                @if (!empty($h['note']))<div class="text-secondary">{{ $h['note'] }}</div>@endif
                            </div>
                            @if (!empty($h['photos']))
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach ($h['photos'] as $p)
                                        <img class="doc-img" style="width: 80px; height: 60px" src="{{ Fmt::file(is_array($p) ? ($p['url'] ?? null) : $p) }}" alt="Ảnh biên bản">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-secondary mb-0">Chưa có biên bản.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Phí phát sinh & quyết toán --}}
        <div class="col-xl-6">
            <div class="card h-100 {{ $status === 'CHO_QUYET_TOAN' ? 'border-warning' : '' }}">
                <div class="card-header"><strong><i class="bi bi-calculator"></i> Phí phát sinh & quyết toán</strong></div>
                <div class="card-body">
                    @if (count($charges))
                        <table class="table table-sm">
                            @foreach ($charges as $c)
                                <tr>
                                    <td>{{ Fmt::label($c['type'] ?? null) }}</td>
                                    <td class="small text-secondary">{{ $c['note'] ?? '' }}</td>
                                    <td class="text-end money">{{ Fmt::money($c['amount'] ?? null) }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-semibold"><td colspan="2">Tổng phí</td><td class="text-end money">{{ Fmt::money(collect($charges)->sum('amount')) }}</td></tr>
                        </table>
                    @else
                        <p class="text-secondary">Chưa có khoản phí nào.</p>
                    @endif

                    @if ($status === 'CHO_QUYET_TOAN')
                        <form method="post" action="{{ route('bookings.settle', $booking['id']) }}" id="settleForm"
                              data-confirm="Chốt quyết toán đơn {{ $booking['code'] }}? Sau khi chốt sẽ sinh lệnh chi cho chủ xe và khách.">
                            @csrf
                            <input type="hidden" name="idem" value="{{ Str::uuid() }}">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="modeAuto" value="auto" @checked(old('mode', 'auto') === 'auto')>
                                <label class="form-check-label" for="modeAuto">Để backend <strong>tự tính phí</strong> từ hai biên bản</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="mode" id="modeManual" value="manual" @checked(old('mode') === 'manual')>
                                <label class="form-check-label" for="modeManual">Nhập phí tay</label>
                            </div>

                            <div id="manualCharges" class="{{ old('mode') === 'manual' ? '' : 'd-none' }}">
                                @for ($i = 0; $i < 3; $i++)
                                    <div class="row g-1 mb-1">
                                        <div class="col-4">
                                            <select class="form-select form-select-sm" name="charges[{{ $i }}][type]">
                                                <option value="">— loại phí —</option>
                                                @foreach (Fmt::CHARGE_TYPES as $code => $label)
                                                    <option value="{{ $code }}" @selected(old("charges.$i.type") === $code)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-3"><input type="number" min="1" step="1" class="form-control form-control-sm" name="charges[{{ $i }}][amount]" value="{{ old("charges.$i.amount") }}" placeholder="Số tiền"></div>
                                        <div class="col-5"><input class="form-control form-control-sm" name="charges[{{ $i }}][note]" value="{{ old("charges.$i.note") }}" placeholder="Ghi chú, VD: quá 30km"></div>
                                    </div>
                                @endfor
                                <div class="small text-secondary">Phí lớn hơn cọc: trừ hết cọc, khách được hoàn 0đ, phần thiếu thành khoản nợ.</div>
                            </div>
                            <button class="btn btn-warning mt-2"><i class="bi bi-lock"></i> Chốt quyết toán</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Lệnh chi --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex"><strong><i class="bi bi-cash-stack"></i> Lệnh chi của đơn</strong>
                    <a href="{{ route('payouts.index', ['status' => '']) }}" class="ms-auto small">Tất cả lệnh chi →</a></div>
                <div class="card-body">
                    @if (count($payouts))
                        <table class="table table-sm mb-0">
                            @foreach ($payouts as $p)
                                <tr>
                                    <td>#{{ $p['id'] }} · {{ Fmt::label($p['payeeType']) }}</td>
                                    <td>{{ $p['payee']['fullName'] ?? '—' }}</td>
                                    <td class="text-end money">{{ Fmt::money($p['amount']) }}</td>
                                    <td><x-status :code="$p['status']" /></td>
                                    <td class="small text-secondary">{{ $p['transferRef'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <p class="text-secondary mb-0">Chưa có lệnh chi.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sổ cái --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header"><strong><i class="bi bi-journal-text"></i> Sổ cái của đơn</strong></div>
                <div class="card-body">
                    @include('partials.ledger-table', ['ledger' => $ledger])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('input[name="mode"]').forEach((r) => r.addEventListener('change', () => {
        document.getElementById('manualCharges').classList.toggle('d-none', document.getElementById('modeManual').checked === false);
    }));
</script>
@endpush

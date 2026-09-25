{{-- Biến: $ledger = { bookingId, entries, tongNo, tongCo, canBang } --}}
@php
    $entries = $ledger['entries'] ?? [];
    $balanced = (bool) ($ledger['canBang'] ?? false);
    // F6 · Tự cộng lại phía web, không chỉ tin cờ canBang của backend.
    $no = collect($entries)->where('direction', 'NO')->sum('amount');
    $co = collect($entries)->where('direction', 'CO')->sum('amount');
    $ok = $balanced && $no === $co;
@endphp

@if (! $ok)
    <div class="alert alert-danger fw-semibold">
        <i class="bi bi-exclamation-octagon-fill"></i> SỔ CÁI KHÔNG CÂN BẰNG — lệch {{ Fmt::money(abs($no - $co)) }}.
        Lệch một đồng cũng không bỏ qua: dừng chi trả đơn này và đối soát sao kê.
    </div>
@endif

@if (count($entries))
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle {{ $ok ? '' : 'table-danger' }}">
            <thead class="table-light">
            <tr>
                <th>Thời điểm</th>
                <th>Tài khoản</th>
                <th>Diễn giải</th>
                <th class="text-end">Nợ</th>
                <th class="text-end">Có</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($entries as $e)
                <tr>
                    <td class="small text-secondary">{{ Fmt::datetime($e['occurredAt'] ?? $e['createdAt'] ?? null) }}</td>
                    <td><span class="badge text-bg-light border">{{ Fmt::label($e['account'] ?? null) }}</span></td>
                    <td class="small">{{ $e['note'] ?? $e['description'] ?? trim(($e['refType'] ?? '').' '.($e['refId'] ?? '')) ?: '—' }}</td>
                    <td class="text-end money">{{ ($e['direction'] ?? '') === 'NO' ? Fmt::money($e['amount']) : '' }}</td>
                    <td class="text-end money">{{ ($e['direction'] ?? '') === 'CO' ? Fmt::money($e['amount']) : '' }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="fw-semibold {{ $ok ? 'table-success' : 'table-danger' }}">
                <td colspan="3">Tổng {!! $ok ? '<i class="bi bi-check-circle"></i> cân bằng' : '<i class="bi bi-x-circle"></i> KHÔNG cân bằng' !!}</td>
                <td class="text-end money">{{ Fmt::money($ledger['tongNo'] ?? $no) }}</td>
                <td class="text-end money">{{ Fmt::money($ledger['tongCo'] ?? $co) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
@else
    <p class="text-secondary mb-0">Chưa có bút toán nào cho đơn này.</p>
@endif

@extends('layouts.admin')

@section('title', 'Sổ cái')

@section('content')
    <form method="get" action="{{ route('ledger.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-sm-4 col-lg-3">
                <label class="form-label small" for="bookingId">Id đơn thuê</label>
                <input type="number" min="1" class="form-control" id="bookingId" name="bookingId" value="{{ $bookingId }}" required>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary"><i class="bi bi-search"></i> Tra sổ cái</button>
            </div>
            <div class="col small text-secondary">
                Dùng khi đối soát sao kê. Bút toán kép: tổng Nợ phải bằng tổng Có. Tài khoản: Khách, Chủ xe, Sàn, Ví treo.
            </div>
        </div>
    </form>

    @if ($ledger)
        <div class="card">
            <div class="card-header d-flex">
                <strong>Đơn #{{ $ledger['bookingId'] }}</strong>
                <a href="{{ route('bookings.show', $ledger['bookingId']) }}" class="ms-auto small">Mở đơn →</a>
            </div>
            <div class="card-body">
                @include('partials.ledger-table', ['ledger' => $ledger])
            </div>
        </div>
    @endif
@endsection

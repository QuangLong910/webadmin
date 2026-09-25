@extends('layouts.admin')

@section('title', 'Không lấy được dữ liệu')

@section('content')
    <div class="alert alert-danger">
        <h2 class="h6"><i class="bi bi-exclamation-octagon"></i> Backend báo lỗi</h2>
        <p class="mb-1">{{ $error->friendlyMessage() }}</p>
        <p class="small text-secondary mb-0">
            HTTP {{ $error->status ?: 'không kết nối' }}@if ($error->errorCode) · mã {{ $error->errorCode }}@endif
        </p>
    </div>
    <a href="{{ url()->current() }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Thử lại</a>
    <a href="{{ route('dashboard') }}" class="btn btn-link">Về tổng quan</a>
@endsection

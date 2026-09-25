{{--
    Nút Duyệt đạt / Từ chối dùng chung cho giấy tờ và xe.
    Biến: $action (URL POST), $id, $reasons (lý do có sẵn), $approveText, $approveConfirm
--}}
@php $open = (int) session('open_reject') === (int) $id; @endphp
<div class="d-flex flex-wrap gap-2">
    <form method="post" action="{{ $action }}" data-confirm="{{ $approveConfirm }}">
        @csrf
        <input type="hidden" name="idem" value="{{ Str::uuid() }}">
        <input type="hidden" name="approved" value="1">
        <button class="btn btn-success"><i class="bi bi-check-lg"></i> {{ $approveText }}</button>
    </form>
    <button class="btn btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#reject-{{ $id }}">
        <i class="bi bi-x-lg"></i> Từ chối…
    </button>
</div>

<form method="post" action="{{ $action }}" class="collapse mt-3 {{ $open ? 'show' : '' }}" id="reject-{{ $id }}">
    @csrf
    <input type="hidden" name="idem" value="{{ Str::uuid() }}">
    <input type="hidden" name="approved" value="0">
    <div class="border border-danger-subtle rounded p-3 bg-danger-subtle bg-opacity-25">
        <label class="form-label fw-semibold">Lý do từ chối <span class="text-danger">*</span></label>
        @foreach ($reasons as $i => $reason)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="reason_preset" id="r-{{ $id }}-{{ $i }}" value="{{ $reason }}"
                       @checked($open && old('reason_preset') === $reason)>
                <label class="form-check-label" for="r-{{ $id }}-{{ $i }}">{{ $reason }}</label>
            </div>
        @endforeach
        <textarea class="form-control mt-2" name="reason_note" rows="2" maxlength="500"
                  placeholder="Ghi thêm cho người nộp biết cần sửa gì (bắt buộc nếu không chọn lý do ở trên)">{{ $open ? old('reason_note') : '' }}</textarea>
        <button class="btn btn-danger mt-2"><i class="bi bi-x-circle"></i> Xác nhận từ chối</button>
    </div>
</form>

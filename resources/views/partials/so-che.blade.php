@if (empty($so))
    —
@else
    {{-- Mặc định che, chỉ lộ số khi người duyệt bấm mắt để đối chiếu với ảnh. --}}
    <span data-so="{{ $so }}" data-che="{{ str_repeat('•', max(strlen($so) - 3, 0)).substr($so, -3) }}">{{ str_repeat('•', max(strlen($so) - 3, 0)).substr($so, -3) }}</span>
    <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline" data-toggle-so title="Hiện/ẩn"><i class="bi bi-eye"></i></button>
@endif

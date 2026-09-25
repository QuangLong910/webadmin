{{-- Biến: $route, $current, $tabs = [mã => nhãn] ('' = tất cả) --}}
<ul class="nav nav-pills flex-wrap gap-1 mb-3">
    @foreach ($tabs as $code => $label)
        <li class="nav-item">
            <a class="nav-link py-1 px-3 {{ (string) $current === (string) $code ? 'active' : 'bg-white' }}"
               href="{{ route($route, ['status' => $code]) }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>

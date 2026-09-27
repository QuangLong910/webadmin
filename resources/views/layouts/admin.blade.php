<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quản trị') · Sàn Thuê Xe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v=1">
    <style>
        body { background: var(--n2); }
        .sidebar { width: 240px; min-height: 100vh; background: var(--n9); }
        .sidebar .nav-link { color: var(--n5); border-radius: .375rem; padding: .55rem .8rem; }
        .sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.06); }
        .sidebar .nav-link.active { color: #fff; background: var(--primary); }
        .sidebar .step { display: inline-block; width: 1.3rem; font-size: .75rem; opacity: .7; }
        .brand { color: #fff; font-weight: 700; letter-spacing: .02em; }
        .doc-img { width: 100%; max-height: 220px; object-fit: contain; background: var(--n3); border-radius: var(--r2); cursor: zoom-in; }
        .money { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .card-queue { border-left: 4px solid var(--warning); }
        @media (max-width: 991.98px) { .sidebar { width: 100%; min-height: auto; } }
    </style>
    @stack('head')
</head>
<body>
<div class="d-lg-flex">
    <aside class="sidebar p-3 flex-shrink-0">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('dashboard') }}" class="brand text-decoration-none"><i class="bi bi-car-front-fill"></i> Sàn Thuê Xe</a>
            <span class="badge text-bg-secondary">Quản trị</span>
        </div>
        @php
            $menu = [
                ['dashboard', 'bi-speedometer2', 'Tổng quan', null],
                ['documents.index', 'bi-person-vcard', 'Duyệt giấy tờ', '1'],
                ['cars.index', 'bi-car-front', 'Duyệt xe', '2'],
                ['bookings.index', 'bi-receipt', 'Đơn thuê & tiền vào', '3'],
                ['payouts.index', 'bi-cash-stack', 'Chi trả', '5'],
                ['ledger.index', 'bi-journal-text', 'Sổ cái', '6'],
            ];
        @endphp
        <nav class="nav flex-column gap-1">
            @foreach ($menu as [$route, $icon, $text, $step])
                <a class="nav-link {{ request()->routeIs(explode('.', $route)[0].'*') ? 'active' : '' }}" href="{{ route($route) }}">
                    <i class="bi {{ $icon }} me-2"></i>{{ $text }}
                </a>
            @endforeach
        </nav>
        <hr class="border-secondary">
        <div class="small text-secondary">
            <i class="bi bi-person-circle"></i> {{ session('api_profile.fullName', 'Người vận hành') }}<br>
            <span class="opacity-75">{{ session('api_profile.phone') }}</span>
        </div>
        <form method="post" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button class="btn btn-sm btn-outline-light w-100"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
        </form>
    </aside>

    <main class="flex-grow-1 p-3 p-lg-4" style="min-width: 0">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h1 class="h4 mb-0">@yield('title')</h1>
            <form class="d-flex" method="get" action="{{ route('bookings.find') }}">
                <input class="form-control form-control-sm" name="q" placeholder="Mã đơn hoặc id đơn…" required>
                <button class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-search"></i></button>
            </form>
        </div>

        @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $color)
            @if (session($key))
                <div class="alert alert-{{ $color }} alert-dismissible fade show" role="alert">
                    {{ session($key) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

{{-- Xem ảnh giấy tờ cỡ lớn --}}
<div class="modal fade" id="imgModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0 py-2 text-white">
                <span id="imgModalTitle" class="small"></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-2 pt-0 text-center">
                <img id="imgModalSrc" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Bấm ảnh giấy tờ để phóng to.
    document.addEventListener('click', (e) => {
        const img = e.target.closest('img.doc-img');
        if (!img) return;
        document.getElementById('imgModalSrc').src = img.src;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('imgModal')).show();
    });
    // Ảnh giấy tờ chỉ tải khi bấm xem; đóng cửa sổ là bỏ ảnh khỏi trang.
    document.addEventListener('click', (e) => {
        const nut = e.target.closest('[data-doc-img]');
        if (!nut) return;
        document.getElementById('imgModalSrc').src = nut.dataset.docImg;
        const tieuDe = document.getElementById('imgModalTitle');
        if (tieuDe) tieuDe.textContent = nut.dataset.docTitle || '';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('imgModal')).show();
    });
    document.getElementById('imgModal')?.addEventListener('hidden.bs.modal', () => {
        document.getElementById('imgModalSrc').removeAttribute('src');
    });
    // Số giấy tờ: che mặc định, bấm mắt để hiện.
    document.addEventListener('click', (e) => {
        const nut = e.target.closest('[data-toggle-so]');
        if (!nut) return;
        const o = nut.previousElementSibling;
        const dangChe = o.textContent === o.dataset.che;
        o.textContent = dangChe ? o.dataset.so : o.dataset.che;
    });
    // Chặn bấm đúp gửi form hai lần; kèm Idempotency-Key ở phía server.
    document.addEventListener('submit', (e) => {
        const btn = e.submitter;
        if (e.target.dataset.confirm && !confirm(e.target.dataset.confirm)) { e.preventDefault(); return; }
        if (btn) { setTimeout(() => { btn.disabled = true; }, 0); }
    });
</script>
@stack('scripts')
</body>
</html>

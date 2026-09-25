<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập · Quản trị Sàn Thuê Xe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-body-tertiary">
<div class="container" style="max-width: 400px">
    <div class="py-5">
        <div class="text-center mb-4">
            <i class="bi bi-car-front-fill fs-1 text-primary"></i>
            <h1 class="h4 mt-2">Quản trị Sàn Thuê Xe</h1>
            <p class="text-secondary small mb-0">Dành cho người vận hành (vai VAN_HANH)</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ url('/login') }}" class="card card-body shadow-sm">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="phone">Số điện thoại</label>
                <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="username" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Mật khẩu</label>
                <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required>
            </div>
            <button class="btn btn-primary w-100">Đăng nhập</button>
        </form>
        <p class="text-center text-secondary small mt-3 mb-0">Backend: {{ config('services.thuexe_api.url') }}</p>
    </div>
</div>
</body>
</html>

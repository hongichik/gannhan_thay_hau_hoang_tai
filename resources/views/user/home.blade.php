<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ gắn nhãn</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Trang home</h1>
                <small class="text-muted">Xin chào {{ auth()->user()->email }}</small>
            </div>
            <form method="POST" action="{{ route('user.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Đăng xuất</button>
            </form>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <a href="{{ route('user.label.intrusions') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body p-4">
                            <h2 class="h5">Khối 1: Gắn nhãn Intrusions</h2>
                            <p class="text-muted mb-0">Chọn 1 từ trong 6 từ cho từng dòng dữ liệu.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('user.label.subdomains') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body p-4">
                            <h2 class="h5">Khối 2: Gắn nhãn Subdomains</h2>
                            <p class="text-muted mb-0">Đọc child/parent và chọn yes hoặc no.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

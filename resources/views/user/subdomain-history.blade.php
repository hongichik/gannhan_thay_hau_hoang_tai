<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử nhãn Subdomains</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <a href="{{ route('user.home') }}" class="btn btn-outline-secondary btn-sm">Quay lại home</a>
                <a href="{{ route('user.label.subdomains') }}" class="btn btn-outline-primary btn-sm">Quay lại trang gắn nhãn mới</a>
            </div>
            <h1 class="h5 mb-0">Lịch sử nhãn Subdomains</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            @if ($currentLabel)
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <span>Đang gắn nhãn lại subdomain đã lưu trước đó. Tiến độ {{ $position }}/{{ $total }}.</span>
                    <a href="{{ route('user.label.subdomains.history', ['edit_subdomain_id' => $currentLabel->subdomain_id, 'relabel' => 1]) }}" class="btn btn-sm btn-warning">Bắt đầu lại từ đây</a>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Subdomain #{{ $currentLabel->subdomain_id }}</h2>

                        @php
                            $childItems = array_values(array_filter(array_map('trim', explode('|', $currentLabel->subdomain?->child ?? ''))));
                            $parentItems = array_values(array_filter(array_map('trim', explode('|', $currentLabel->subdomain?->parent ?? ''))));
                        @endphp

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Child</label>
                            <div class="form-control bg-white" style="min-height: 60px; height: auto;">
                                @if (count($childItems) > 0)
                                    <ul class="mb-0 ps-3">
                                        @foreach ($childItems as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">Không có dữ liệu</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Parent</label>
                            <div class="form-control bg-white" style="min-height: 60px; height: auto;">
                                @if (count($parentItems) > 0)
                                    <ul class="mb-0 ps-3">
                                        @foreach ($parentItems as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">Không có dữ liệu</span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('user.label.subdomains.store') }}" class="w-50">
                                @csrf
                                <input type="hidden" name="subdomain_id" value="{{ $currentLabel->subdomain_id }}">
                                <input type="hidden" name="label" value="yes">
                                <input type="hidden" name="relabel_mode" value="1">
                                <button type="submit" class="btn w-100 {{ $currentLabel->label === 'yes' ? 'btn-success' : 'btn-outline-success' }}">Yes</button>
                            </form>

                            <form method="POST" action="{{ route('user.label.subdomains.store') }}" class="w-50">
                                @csrf
                                <input type="hidden" name="subdomain_id" value="{{ $currentLabel->subdomain_id }}">
                                <input type="hidden" name="label" value="no">
                                <input type="hidden" name="relabel_mode" value="1">
                                <button type="submit" class="btn w-100 {{ $currentLabel->label === 'no' ? 'btn-danger' : 'btn-outline-danger' }}">No</button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info mb-0">Bạn chưa có subdomain nào đã gắn nhãn để gắn lại.</div>
            @endif
    </div>
</body>
</html>

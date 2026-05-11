<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gắn nhãn Subdomains</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <a href="{{ route('user.home') }}" class="btn btn-outline-secondary btn-sm">Quay lại home</a>
                <a href="{{ route('user.label.subdomains.history') }}" class="btn btn-outline-primary btn-sm">Xem nhãn đã gắn</a>
            </div>
            <div class="text-muted small">Tiến độ: {{ $done }}/{{ $total }}</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($editingSubdomainId)
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span>
                    @if ($relabelMode)
                        Bạn đang ở chế độ gắn nhãn lại từ đầu cho subdomain #{{ $editingSubdomainId }}.
                    @else
                        Bạn đang xem lại nhãn của subdomain #{{ $editingSubdomainId }}.
                    @endif
                </span>
                <a href="{{ $relabelMode ? route('user.label.subdomains.history') : route('user.label.subdomains') }}" class="btn btn-sm btn-outline-primary">
                    {{ $relabelMode ? 'Quay về lịch sử nhãn' : 'Quay về chế độ gắn nhãn tiếp theo' }}
                </a>
            </div>
        @endif

        @if ($total === 0)
            <div class="alert alert-warning mb-0">Chưa có dữ liệu subdomains để gắn nhãn. Vui lòng import dữ liệu trước.</div>
        @elseif ($subdomain)
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h5 mb-3">Gắn nhãn Subdomain #{{ $subdomain->id }}</h1>

                    @php
                        $childItems = array_values(array_filter(array_map('trim', explode('|', $subdomain->child))));
                        $parentItems = array_values(array_filter(array_map('trim', explode('|', $subdomain->parent))));
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
                            <input type="hidden" name="subdomain_id" value="{{ $subdomain->id }}">
                            <input type="hidden" name="label" value="yes">
                            <input type="hidden" name="relabel_mode" value="{{ $relabelMode ? 1 : 0 }}">
                            <button type="submit" class="btn w-100 {{ ($currentLabel && $currentLabel->label === 'yes') ? 'btn-success' : 'btn-outline-success' }}">Yes</button>
                        </form>

                        <form method="POST" action="{{ route('user.label.subdomains.store') }}" class="w-50">
                            @csrf
                            <input type="hidden" name="subdomain_id" value="{{ $subdomain->id }}">
                            <input type="hidden" name="label" value="no">
                            <input type="hidden" name="relabel_mode" value="{{ $relabelMode ? 1 : 0 }}">
                            <button type="submit" class="btn w-100 {{ ($currentLabel && $currentLabel->label === 'no') ? 'btn-danger' : 'btn-outline-danger' }}">No</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-0">Bạn đã gắn nhãn hết toàn bộ dữ liệu subdomains.</div>
        @endif

    </div>
</body>
</html>

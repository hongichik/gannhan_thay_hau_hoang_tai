<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử nhãn Intrusions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <a href="{{ route('user.home') }}" class="btn btn-outline-secondary btn-sm">Quay lại home</a>
                <a href="{{ route('user.label.intrusions') }}" class="btn btn-outline-primary btn-sm">Quay lại trang gắn nhãn mới</a>
            </div>
            <h1 class="h5 mb-0">Lịch sử nhãn Intrusions</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($currentLabel)
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span>Đang gắn nhãn lại intrusion đã lưu trước đó. Tiến độ {{ $position }}/{{ $total }}.</span>
                <a href="{{ route('user.label.intrusions.history', ['edit_intrusion_id' => $currentLabel->intrusion_id, 'relabel' => 1]) }}" class="btn btn-sm btn-warning">Bắt đầu lại từ đây</a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Intrusion #{{ $currentLabel->intrusion_id }}</h2>
                    <p class="text-muted">Chọn lại 1 từ trong 6 từ. Sau khi lưu sẽ tự chuyển sang intrusion đã gắn tiếp theo.</p>

                    <div class="row g-2">
                        @for ($i = 1; $i <= 6; $i++)
                            @php
                                $key = 'word_' . $i;
                                $value = $currentLabel->intrusion?->$key;
                            @endphp
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('user.label.intrusions.store') }}">
                                    @csrf
                                    <input type="hidden" name="intrusion_id" value="{{ $currentLabel->intrusion_id }}">
                                    <input type="hidden" name="outlier_id" value="{{ $i }}">
                                    <input type="hidden" name="relabel_mode" value="1">
                                    <button type="submit" class="btn w-100 text-start {{ (int) $currentLabel->outlier_id === $i ? 'btn-primary' : 'btn-outline-primary' }}" {{ $value ? '' : 'disabled' }}>
                                        <strong>Word {{ $i }}:</strong>
                                        <div>{{ $value ?: 'Không có dữ liệu' }}</div>
                                    </button>
                                </form>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-0">Bạn chưa có intrusion nào đã gắn nhãn để gắn lại.</div>
        @endif
    </div>
</body>
</html>

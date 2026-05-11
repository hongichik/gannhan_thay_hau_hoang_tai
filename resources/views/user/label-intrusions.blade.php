<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gắn nhãn Intrusions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <a href="{{ route('user.home') }}" class="btn btn-outline-secondary btn-sm">Quay lại home</a>
                <a href="{{ route('user.label.intrusions.history') }}" class="btn btn-outline-primary btn-sm">Xem nhãn đã gắn</a>
            </div>
            <div class="text-muted small">Tiến độ: {{ $done }}/{{ $total }}</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($editingIntrusionId)
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span>
                    @if ($relabelMode)
                        Bạn đang ở chế độ gắn nhãn lại từ đầu cho intrusion #{{ $editingIntrusionId }}.
                    @else
                        Bạn đang xem lại nhãn của intrusion #{{ $editingIntrusionId }}.
                    @endif
                </span>
                <a href="{{ $relabelMode ? route('user.label.intrusions.history') : route('user.label.intrusions') }}" class="btn btn-sm btn-outline-primary">
                    {{ $relabelMode ? 'Quay về lịch sử nhãn' : 'Quay về chế độ gắn nhãn tiếp theo' }}
                </a>
            </div>
        @endif

        @if ($total === 0)
            <div class="alert alert-warning mb-0">Chưa có dữ liệu intrusions để gắn nhãn. Vui lòng import dữ liệu trước.</div>
        @elseif ($intrusion)
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h5 mb-3">Gắn nhãn Intrusion #{{ $intrusion->id }}</h1>
                    <p class="text-muted">Chọn đúng 1 từ dưới đây:</p>

                    <div class="row g-2">
                        @for ($i = 1; $i <= 6; $i++)
                            @php
                                $key = 'word_' . $i;
                                $value = $intrusion->$key;
                            @endphp
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('user.label.intrusions.store') }}">
                                    @csrf
                                    <input type="hidden" name="intrusion_id" value="{{ $intrusion->id }}">
                                    <input type="hidden" name="outlier_id" value="{{ $i }}">
                                    <input type="hidden" name="relabel_mode" value="{{ $relabelMode ? 1 : 0 }}">
                                    <button type="submit" class="btn w-100 text-start {{ ($currentLabel && (int) $currentLabel->outlier_id === $i) ? 'btn-primary' : 'btn-outline-primary' }}" {{ $value ? '' : 'disabled' }}>
                                        <strong>Word {{ $i }}:</strong>
                                        <div>{{ $value ?: 'Không có dữ liệu' }}</div>
                                    </button>
                                </form>
                            </div>
                        @endfor
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        @if ($prevIntrusion)
                            <a href="{{ route('user.label.intrusions', ['edit_intrusion_id' => $prevIntrusion->id, 'relabel' => $relabelMode ? 1 : 0]) }}" class="btn btn-outline-secondary">&larr; Trước</a>
                        @else
                            <span></span>
                        @endif
                        @if ($nextIntrusion)
                            <a href="{{ route('user.label.intrusions', ['edit_intrusion_id' => $nextIntrusion->id, 'relabel' => $relabelMode ? 1 : 0]) }}" class="btn btn-outline-secondary">Tiếp &rarr;</a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-0">Bạn đã gắn nhãn hết toàn bộ dữ liệu intrusions.</div>
        @endif

    </div>
</body>
</html>

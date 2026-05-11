@extends('layouts.layout-master')

@section('title', 'Quản lý Intrusions')
@section('page_title', 'Quản lý Intrusions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Import CSV và danh sách Intrusions</h3>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.data.intrusions.import') }}" enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">File CSV Intrusions</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                            <small class="text-muted">Header có thể là: 0,1,2,3,4,5,outlier id</small>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-file-import"></i> Import Intrusions
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" form="clear-intrusions-form" class="btn btn-danger w-100" onclick="return confirm('Bạn chắc chắn muốn xoá toàn bộ dữ liệu intrusions?')">
                                <i class="fas fa-trash"></i> Xoá tất cả
                            </button>
                        </div>
                    </div>
                </form>

                <form id="clear-intrusions-form" method="POST" action="{{ route('admin.data.intrusions.clear') }}" class="d-none">
                    @csrf
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="intrusions-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>word_1</th>
                                <th>word_2</th>
                                <th>word_3</th>
                                <th>word_4</th>
                                <th>word_5</th>
                                <th>word_6</th>
                                <th>outlier_id</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h4 class="h6 mb-0">Xuất danh sách nhãn theo người dùng</h4>
                    <a href="{{ route('admin.data.intrusions.export-all') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-archive"></i> Xuất tất cả
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th width="180">Số nhãn đã gắn</th>
                                <th width="220">Xuất file</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($intrusionLabelUsers as $user)
                                <tr>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->labels_count }}</td>
                                    <td>
                                        <a href="{{ route('admin.data.intrusions.export', $user->id) }}" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-file-export"></i> Xuất CSV
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Chưa có người dùng nào gắn nhãn intrusions.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#intrusions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('admin.data.intrusions.index') !!}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'word_1', name: 'word_1' },
            { data: 'word_2', name: 'word_2' },
            { data: 'word_3', name: 'word_3' },
            { data: 'word_4', name: 'word_4' },
            { data: 'word_5', name: 'word_5' },
            { data: 'word_6', name: 'word_6' },
            { data: 'outlier_id', name: 'outlier_id' }
        ]
    });
});
</script>
@endpush

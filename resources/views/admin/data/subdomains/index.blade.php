@extends('layouts.layout-master')

@section('title', 'Quản lý Subdomains')
@section('page_title', 'Quản lý Subdomains')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Import CSV và danh sách Subdomains</h3>
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

                <form method="POST" action="{{ route('admin.data.subdomains.import') }}" enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">File CSV Subdomains</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                            <small class="text-muted">Header mẫu: child,parent,y or n? (cột label có thể để trống, mặc định no)</small>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-file-import"></i> Import Subdomains
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" form="clear-subdomains-form" class="btn btn-danger w-100" onclick="return confirm('Bạn chắc chắn muốn xoá toàn bộ dữ liệu subdomains?')">
                                <i class="fas fa-trash"></i> Xoá tất cả
                            </button>
                        </div>
                    </div>
                </form>

                <form id="clear-subdomains-form" method="POST" action="{{ route('admin.data.subdomains.clear') }}" class="d-none">
                    @csrf
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="subdomains-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>child</th>
                                <th>parent</th>
                                <th>label</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h4 class="h6 mb-0">Xuất danh sách nhãn theo người dùng</h4>
                    <a href="{{ route('admin.data.subdomains.export-all') }}" class="btn btn-outline-success btn-sm">
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
                            @forelse ($subdomainLabelUsers as $user)
                                <tr>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->labels_count }}</td>
                                    <td>
                                        <a href="{{ route('admin.data.subdomains.export', $user->id) }}" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-file-export"></i> Xuất CSV
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Chưa có người dùng nào gắn nhãn subdomains.</td>
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
    $('#subdomains-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('admin.data.subdomains.index') !!}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'child', name: 'child' },
            { data: 'parent', name: 'parent' },
            { data: 'label_badge', name: 'label', orderable: true, searchable: false }
        ]
    });
});
</script>
@endpush

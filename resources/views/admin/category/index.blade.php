@extends('admin.layouts.app')

@section('title', 'Master Kategori Produk')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Kategori Produk</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.category.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Tambah Kategori
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px">No</th>
                                    <th style="width: 60px">Icon</th>
                                    <th>Nama Kategori</th>
                                    <th>Slug</th>
                                    <th>Jumlah Produk</th>
                                    <th style="width: 100px">Status</th>
                                    <th style="width: 150px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $index => $category)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-center">
                                            @if($category->icon)
                                                <i class="{{ $category->icon }} fa-lg"></i>
                                            @else
                                                <i class="fas fa-folder fa-lg text-muted"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $category->name }}</strong>
                                            @if($category->description)
                                                <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td><code>{{ $category->slug }}</code></td>
                                        <td>
                                            <span class="badge badge-info">{{ $category->products_count }} produk</span>
                                        </td>
                                        <td>
                                            @if($category->is_active)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.category.edit', $category->id) }}"
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmDelete({{ $category->id }}, '{{ $category->name }}')"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $category->id }}"
                                                  action="{{ route('admin.category.destroy', $category->id) }}"
                                                  method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>Belum ada kategori. <a href="{{ route('admin.category.create') }}">Tambah kategori baru</a></p>
                                        </td>
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

@push('javascript')
<script>
    function confirmDelete(id, name) {
        if (confirm('Apakah Anda yakin ingin menghapus kategori "' + name + '"?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush

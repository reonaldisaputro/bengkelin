@extends('admin.layouts.app')

@section('title', 'Edit Kategori')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Form Edit Kategori</h3>
                    </div>
                    <form action="{{ route('admin.category.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $category->name) }}"
                                       placeholder="Contoh: Oli & Pelumas" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="icon">Icon (Font Awesome Class)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="icon-preview">
                                            <i class="{{ $category->icon ?: 'fas fa-folder' }}"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                           id="icon" name="icon" value="{{ old('icon', $category->icon) }}"
                                           placeholder="Contoh: fas fa-oil-can">
                                </div>
                                <small class="form-text text-muted">
                                    Lihat daftar icon di <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a>
                                </small>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Deskripsi singkat kategori...">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active"
                                           name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Aktif</label>
                                </div>
                                <small class="form-text text-muted">
                                    Kategori yang tidak aktif tidak akan ditampilkan di aplikasi.
                                </small>
                            </div>

                            <div class="callout callout-info">
                                <h5><i class="fas fa-info-circle"></i> Informasi</h5>
                                <p class="mb-0">
                                    Slug: <code>{{ $category->slug }}</code><br>
                                    Jumlah Produk: <strong>{{ $category->products()->count() }}</strong><br>
                                    Dibuat: {{ $category->created_at->format('d M Y H:i') }}<br>
                                    Diperbarui: {{ $category->updated_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('admin.category.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Contoh Icon</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Berikut beberapa contoh icon yang bisa digunakan:</p>
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-oil-can fa-lg"></i></td>
                                    <td><code>fas fa-oil-can</code></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-car-battery fa-lg"></i></td>
                                    <td><code>fas fa-car-battery</code></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-tire fa-lg"></i></td>
                                    <td><code>fas fa-tire</code></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-cogs fa-lg"></i></td>
                                    <td><code>fas fa-cogs</code></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-wrench fa-lg"></i></td>
                                    <td><code>fas fa-wrench</code></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-toolbox fa-lg"></i></td>
                                    <td><code>fas fa-toolbox</code></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-spray-can fa-lg"></i></td>
                                    <td><code>fas fa-spray-can</code></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-lightbulb fa-lg"></i></td>
                                    <td><code>fas fa-lightbulb</code></td>
                                </tr>
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
    document.getElementById('icon').addEventListener('input', function() {
        const iconClass = this.value || 'fas fa-folder';
        document.querySelector('#icon-preview i').className = iconClass;
    });
</script>
@endpush

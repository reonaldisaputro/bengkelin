@extends('admin.layouts.app')

@section('title', 'Edit Merk Mobil')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Merk Mobil</h3>
                        <div class="card-tools">
                            <a href="{{ route('merk-mobil.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <form action="{{ route('merk-mobil.update', $merkMobil->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="nama_merk">Nama Merk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_merk') is-invalid @enderror"
                                       id="nama_merk" name="nama_merk" value="{{ old('nama_merk', $merkMobil->nama_merk) }}"
                                       placeholder="Contoh: Toyota, Honda, dll" required>
                                @error('nama_merk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="logo">Logo</label>
                                @if($merkMobil->logo)
                                    <div class="mb-2">
                                        <img src="{{ $merkMobil->logo_url }}" alt="{{ $merkMobil->nama_merk }}" class="img-thumbnail" style="max-width: 200px;">
                                        <p class="text-muted small mt-1">Logo saat ini</p>
                                    </div>
                                @endif
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('logo') is-invalid @enderror"
                                           id="logo" name="logo" accept="image/*" onchange="previewImage(event)">
                                    <label class="custom-file-label" for="logo">Pilih file baru...</label>
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah logo.</small>
                                <div class="mt-2">
                                    <img id="preview" src="" alt="Preview" style="max-width: 200px; display: none;" class="img-thumbnail">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="deskripsi">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                          id="deskripsi" name="deskripsi" rows="4"
                                          placeholder="Masukkan deskripsi merk mobil">{{ old('deskripsi', $merkMobil->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="{{ route('merk-mobil.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('preview');
        const label = input.nextElementSibling;

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
            label.textContent = input.files[0].name;
        }
    }
</script>
@endpush

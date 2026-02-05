@extends('admin.layouts.app')

@section('title', 'Master Merk Mobil')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Merk Mobil</h3>
                        <div class="card-tools">
                            <a href="{{ route('merk-mobil.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Tambah Merk Mobil
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px">No</th>
                                    <th style="width: 100px">Logo</th>
                                    <th>Nama Merk</th>
                                    <th>Deskripsi</th>
                                    <th>Jumlah Bengkel</th>
                                    <th style="width: 150px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($merkMobils as $index => $merk)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-center">
                                            @if($merk->logo)
                                                <img src="{{ $merk->logo_url }}" alt="{{ $merk->nama_merk }}" class="img-thumbnail" style="max-width: 80px; max-height: 80px;">
                                            @else
                                                <i class="fas fa-car fa-3x text-muted"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $merk->nama_merk }}</strong>
                                        </td>
                                        <td>
                                            {{ $merk->deskripsi ? Str::limit($merk->deskripsi, 100) : '-' }}
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $merk->bengkels->count() }} bengkel</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('merk-mobil.show', $merk->id) }}"
                                               class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('merk-mobil.edit', $merk->id) }}"
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('merk-mobil.destroy', $merk->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus merk mobil ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="alert alert-info mb-0">
                                                Belum ada data merk mobil
                                            </div>
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

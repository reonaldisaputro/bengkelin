@extends('admin.layouts.app')

@section('title', 'Detail Merk Mobil')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Merk Mobil</h3>
                        <div class="card-tools">
                            <a href="{{ route('merk-mobil.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if($merkMobil->logo)
                                <img src="{{ $merkMobil->logo_url }}" alt="{{ $merkMobil->nama_merk }}" class="img-thumbnail" style="max-width: 200px;">
                            @else
                                <i class="fas fa-car fa-5x text-muted"></i>
                            @endif
                        </div>

                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 40%">Nama Merk:</th>
                                <td><strong>{{ $merkMobil->nama_merk }}</strong></td>
                            </tr>
                            <tr>
                                <th>Deskripsi:</th>
                                <td>{{ $merkMobil->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Bengkel:</th>
                                <td>
                                    <span class="badge badge-info">{{ $merkMobil->bengkels->count() }} bengkel</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Dibuat:</th>
                                <td>{{ $merkMobil->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Update:</th>
                                <td>{{ $merkMobil->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </table>

                        <div class="mt-3">
                            <a href="{{ route('merk-mobil.edit', $merkMobil->id) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Edit Merk Mobil
                            </a>
                            <form action="{{ route('merk-mobil.destroy', $merkMobil->id) }}"
                                  method="POST" class="mt-2"
                                  onsubmit="return confirm('Yakin ingin menghapus merk mobil ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fas fa-trash"></i> Hapus Merk Mobil
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bengkel yang Menangani {{ $merkMobil->nama_merk }}</h3>
                    </div>
                    <div class="card-body">
                        @if($merkMobil->bengkels->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px">No</th>
                                            <th>Nama Bengkel</th>
                                            <th>Alamat</th>
                                            <th>Pemilik</th>
                                            <th style="width: 100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($merkMobil->bengkels as $index => $bengkel)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $bengkel->name }}</strong>
                                                </td>
                                                <td>{{ Str::limit($bengkel->alamat, 50) }}</td>
                                                <td>{{ $bengkel->pemilik_bengkel->name ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('detailbengkel', $bengkel->id) }}" 
                                                       class="btn btn-sm btn-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada bengkel yang menangani merk mobil ini.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

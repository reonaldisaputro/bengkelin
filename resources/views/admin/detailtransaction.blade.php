@extends('admin.layouts.app')

@section('title', 'Detail Transaksi')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Transaksi</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/owner">Home</a></li>
                        <li class="breadcrumb-item active">Detail Transaksi</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h5>Detail Transaksi {{ $transaction->transaction_code }}</h5>
                </div>
                <div class="card-body">
                    <div>
                        <div class="detail-booking d-flex justify-content-between align-items-center">
                            <p style="margin: 0">Transaction Code</p>
                            <p style="margin: 0" class="fw-bold">{{ $transaction->transaction_code }}</p>
                        </div>
                        <div class="detail-booking d-flex justify-content-between align-items-center">
                            <p style="margin: 0">Status Pembayaran</p>
                            <p style="margin: 0" class="fw-bold">
                                <span class="badge badge-{{ $transaction->payment_status == 'Success' ? 'success' : ($transaction->payment_status == 'Pending' ? 'warning' : 'danger') }}">
                                    {{ $transaction->payment_status }}
                                </span>
                            </p>
                        </div>
                        <div class="detail-booking d-flex justify-content-between align-items-center">
                            <p style="margin: 0">Status Pengiriman</p>
                            <p style="margin: 0" class="fw-bold">
                                <span class="badge badge-{{ $transaction->shipping_status == 'Delivered' ? 'success' : ($transaction->shipping_status == 'Cancelled' ? 'danger' : 'info') }}">
                                    {{ $transaction->shipping_status }}
                                </span>
                            </p>
                        </div>
                        <div class="detail-booking d-flex justify-content-between align-items-center">
                            <p style="margin: 0">Total Belanja</p>
                            <p style="margin: 0" class="fw-bold">
                                Rp{{ number_format($transaction->grand_total - ($transaction->ongkir + $transaction->administrasi)) }}
                            </p>
                        </div>
                    </div>

                    <!-- Form Edit Status -->
                    <div class="mt-4">
                        <h5>Update Status Transaksi</h5>
                        <form action="{{ route('admin.update.transaction', $transaction->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_status">Status Pembayaran</label>
                                        <select name="payment_status" id="payment_status" class="form-control @error('payment_status') is-invalid @enderror" required>
                                            <option value="Pending" {{ $transaction->payment_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="Success" {{ $transaction->payment_status == 'Success' ? 'selected' : '' }}>Success</option>
                                            <option value="Cancelled" {{ $transaction->payment_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        @error('payment_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_status">Status Pengiriman</label>
                                        <select name="shipping_status" id="shipping_status" class="form-control @error('shipping_status') is-invalid @enderror" required>
                                            <option value="Pending" {{ $transaction->shipping_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="Processing" {{ $transaction->shipping_status == 'Processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="Shipped" {{ $transaction->shipping_status == 'Shipped' ? 'selected' : '' }}>Shipped</option>
                                            <option value="Delivered" {{ $transaction->shipping_status == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                            <option value="Cancelled" {{ $transaction->shipping_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        @error('shipping_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                            <a href="{{ route('showlisttransaction') }}" class="btn btn-secondary">Kembali</a>
                        </form>
                    </div>
                    <div class="mt-4">
                        <h5>Informasi Tambahan</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Nama User</th>
                                    <th scope="col">Alamat User</th>
                                    {{-- <th scope="col">Catatan Tambahan</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <p>{{ $transaction->user->name }}</p>
                                    </td>
                                    <td>
                                        <p>{{ $transaction->user->alamat }}</p>
                                    </td>
                                    {{-- <td>
                                        <p>{{ $transaction->catatan_tambahan }}</p>
                                    </td> --}}
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <h5>Detail Transaksi</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Nama Produk / Layanan</th>
                                    <th scope="col">Kuantitas</th>
                                    <th scope="col">Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $detail)
                                    <tr>
                                        <td>{{ $detail->product ? $detail->product->name : ($detail->layanan ? $detail->layanan->name : 'N/A') }}
                                        </td>
                                        <td>{{ $detail->qty }}</td>
                                        <td>Rp{{ number_format($detail->product ? $detail->product_price * $detail->qty : $detail->layanan_price * $detail->qty) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

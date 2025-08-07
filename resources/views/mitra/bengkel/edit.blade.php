@extends('mitra.layouts.app')

@section('title', 'Edit Bengkel')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Bengkel</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/owner">Home</a></li>
                        <li class="breadcrumb-item active">EditBengkel</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit Bengkel</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                {{-- <form action="/owner/bengkel/{{ $bengkel->id }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="bengkel_name">Nama Bengkel</label>
                            <input type="text" class="form-control" id="bengkel_name" name="bengkel_name"
                                placeholder="Nama bengkel" value="{{ $bengkel->name }}">
                        </div>
                        <div class="form-group">
                            <label>Spesialis Bengkel</label>
                            <div class="row">
                                @foreach ($specialists as $specialist)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialist_ids[]"
                                                value="{{ $specialist->id }}">
                                            <label class="form-check-label">{{ $specialist->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Deskripsi Bengkel</label>
                            <textarea rows="5" type="text" class="form-control" id="bengkel_description" name="bengkel_description"
                                placeholder="Deskripsi bengkel">{{ $bengkel->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Kecamatan</label>
                            <select class="custom-select" name="kecamatan_id" id="kecamatan_id">
                                <option>-- Pilih Kecamatan --</option>
                                @foreach ($kecamatans as $kecamatan)
                                    <option value="{{ $kecamatan->id }}">{{ $kecamatan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Kelurahan</label>
                            <select class="custom-select" name="kelurahan_id" id="kelurahan_id">
                                <option selected>-- Pilih Kelurahan --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bengkel_address">Alamat Bengkel</label>
                            <textarea rows="10" type="text" class="form-control" id="bengkel_address" name="bengkel_address"
                                placeholder="Alamat bengkel">{{ $bengkel->alamat }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" 
                                           value="{{ old('latitude', $bengkel->latitude) }}" placeholder="Akan terisi otomatis dari peta">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" 
                                           value="{{ old('longitude', $bengkel->longitude) }}" placeholder="Akan terisi otomatis dari peta">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                             <label>Pilih Lokasi di Peta</label>
                             <div id="map" style="height: 400px; width: 100%; border-radius: 5px;"></div>
                             <small class="form-text text-muted">Klik pada peta atau geser penanda untuk menentukan lokasi persis bengkel Anda.</small>
                        </div>

                        <div class="form-group">
                            <label for="image">Gambar Bengkel</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image"
                                        value="{{ $bengkel->image }}">
                                    <label class="custom-file-label" for="image">Cari Foto</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form> --}}
                <form action="/owner/bengkel/{{ $bengkel->id }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="bengkel_name">Nama Bengkel</label>
                            <input type="text" class="form-control" id="bengkel_name" name="bengkel_name"
                                placeholder="Nama bengkel" value="{{ old('bengkel_name', $bengkel->name) }}">
                        </div>
                        <div class="form-group">
                            <label>Spesialis Bengkel</label>
                            <div class="row">
                                @foreach ($specialists as $specialist)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialist_ids[]"
                                                value="{{ $specialist->id }}"
                                                {{ in_array($specialist->id, $bengkel->specialists->pluck('id')->toArray()) ? 'checked' : '' }}>
                                            <label class="form-check-label">{{ $specialist->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Deskripsi Bengkel</label>
                            <textarea rows="5" type="text" class="form-control" id="bengkel_description" name="bengkel_description"
                                placeholder="Deskripsi bengkel">{{ old('bengkel_description', $bengkel->description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Kecamatan</label>
                            <select class="custom-select" name="kecamatan_id" id="kecamatan_id">
                                <option>-- Pilih Kecamatan --</option>
                                @foreach ($kecamatans as $kecamatan)
                                    <option value="{{ $kecamatan->id }}"
                                        {{ $bengkel->kecamatan_id == $kecamatan->id ? 'selected' : '' }}>
                                        {{ $kecamatan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Kelurahan</label>
                            <select class="custom-select" name="kelurahan_id" id="kelurahan_id">
                                <option selected>-- Pilih Kelurahan --</option>
                                @foreach ($kelurahans as $kelurahan)
                                    <option value="{{ $kelurahan->id }}"
                                        {{ $bengkel->kelurahan_id == $kelurahan->id ? 'selected' : '' }}>
                                        {{ $kelurahan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bengkel_address">Alamat Bengkel</label>
                            <textarea rows="10" type="text" class="form-control" id="bengkel_address" name="bengkel_address"
                                placeholder="Alamat bengkel">{{ old('bengkel_address', $bengkel->alamat) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" 
                                           value="{{ old('latitude', $bengkel->latitude) }}" placeholder="Akan terisi otomatis dari peta">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" 
                                           value="{{ old('longitude', $bengkel->longitude) }}" placeholder="Akan terisi otomatis dari peta">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                             <label>Pilih Lokasi di Peta</label>
                             <div id="map" style="height: 400px; width: 100%; border-radius: 5px;"></div>
                             <small class="form-text text-muted">Klik pada peta atau geser penanda untuk menentukan lokasi persis bengkel Anda.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Gambar Bengkel</label>
                            <img id="image-preview" src="{{ asset('storage/' . $bengkel->image) }}" alt="Image Preview"
                                style="display: block; margin-top: 10px; max-width: 400px; max-height: 400px;"
                                class="mb-2">
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image">
                                    <label class="custom-file-label" for="image">Cari Foto</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>

            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
@push('javascript')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script>
        $(document).ready(function() {
            $('#kecamatan_id').change(function() {
                var kecamatan_id = $(this).val();
                $.ajax({
                    url: '/owner/get-kelurahan/' + kecamatan_id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#kelurahan_id').empty();
                        $('#kelurahan_id').append(
                            '<option selected>-- Pilih Kelurahan --</option>');
                        $.each(data, function(key, value) {
                            $('#kelurahan_id').append('<option value="' + value.id +
                                '">' + value.name + '</option>');
                        });
                    }
                });
            });
        });
    </script>
    <script>
        document.getElementById('image').addEventListener('change', function(event) {
            var file = event.target.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                var preview = document.getElementById('image-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };

            reader.readAsDataURL(file);
        });
    </script>

    <script>
        // 1. Inisialisasi Peta
        // Ambil nilai lat/long yang sudah ada, atau gunakan default (Jakarta)
        var initialLat = {{ $bengkel->latitude ?? -6.2088 }};
        var initialLng = {{ $bengkel->longitude ?? 106.8456 }};
        var map = L.map('map').setView([initialLat, initialLng], 15); // Angka 15 adalah level zoom

        // 2. Tambahkan Tile Layer (Tampilan Peta) dari OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // 3. Buat Penanda (Marker)
        var marker = L.marker([initialLat, initialLng], {
            draggable: true // Membuat penanda bisa digeser
        }).addTo(map);

        // Fungsi untuk memperbarui input field saat penanda digeser
        function onMarkerDragEnd(e) {
            var latlng = e.target.getLatLng();
            document.getElementById('latitude').value = latlng.lat.toFixed(8);
            document.getElementById('longitude').value = latlng.lng.toFixed(8);
        }

        // Fungsi untuk memindahkan penanda saat peta diklik
        function onMapClick(e) {
            var latlng = e.latlng;
            marker.setLatLng(latlng);
            document.getElementById('latitude').value = latlng.lat.toFixed(8);
            document.getElementById('longitude').value = latlng.lng.toFixed(8);
        }
        
        // Tambahkan event listener
        marker.on('dragend', onMarkerDragEnd);
        map.on('click', onMapClick);

        // Bonus: Update peta jika input lat/long diubah manual
        document.getElementById('latitude').addEventListener('change', function(e){
            updateMapFromInputs();
        });
        document.getElementById('longitude').addEventListener('change', function(e){
            updateMapFromInputs();
        });

        function updateMapFromInputs(){
            var lat = parseFloat(document.getElementById('latitude').value);
            var lng = parseFloat(document.getElementById('longitude').value);
            if (!isNaN(lat) && !isNaN(lng)) {
                var newLatLng = L.latLng(lat, lng);
                marker.setLatLng(newLatLng);
                map.panTo(newLatLng);
            }
        }
    </script>
@endpush

<?php

namespace App\Http\Controllers;

use App\Models\Bengkel;
use App\Models\Booking;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use App\Models\PemilikBengkel;
use App\Models\Specialist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BengkelController extends Controller
{
    public function index()
    {
        $data['bengkels'] = Bengkel::where('pemilik_id', Auth::id())->get();
        $item['owner'] = PemilikBengkel::all();
        return view('mitra.bengkel.index', $data, $item);
    }

    public function create()
    {
        $data = PemilikBengkel::orderBy('name', 'ASC')->get();
        $kecamatans = Kecamatan::all();
        $specialists = Specialist::all(); // Ambil data specialists
        return view('mitra.bengkel.add', [
            'data' => $data,
            'kecamatans' => $kecamatans,
            'specialists' => $specialists // Kirim data specialists ke view
        ]);
    }

    public function getKelurahans($kecamatan_id)
    {
        $kelurahans = Kelurahan::where('kecamatan_id', $kecamatan_id)->get();
        return response()->json($kelurahans);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bengkel_name' => 'required|string|max:255',
            'bengkel_description' => 'required|string',
            'bengkel_address' => 'required|string',
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'kelurahan_id' => 'required|exists:kelurahans,id',
            'image' => 'required|image|max:2048',
            'specialist_ids' => 'required|array', // Validasi untuk specialist
            'specialist_ids.*' => 'exists:specialists,id', // Validasi setiap ID specialist
        ]);

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('images'), $imageName);

        $owner = Auth::user();
        $owner_id = $owner->id;
        $bengkels = new Bengkel();
        $bengkels->name = $request->bengkel_name;
        $bengkels->description = $request->bengkel_description;
        $bengkels->alamat = $request->bengkel_address;
        $bengkels->image = $imageName;
        $bengkels->pemilik_id = $owner_id;
        $bengkels->kecamatan_id = $request->kecamatan_id;
        $bengkels->kelurahan_id = $request->kelurahan_id;

        $bengkels->save();

        // Menyimpan relasi dengan specialists
        $bengkels->specialists()->sync($request->specialist_ids);

        return redirect('owner/bengkel')->with('success', 'Bengkel berhasil ditambahkan');
    }


    public function edit($id)
    {
        $bengkel = Bengkel::findOrFail($id);
        $kecamatans = Kecamatan::all();
        $kelurahans = Kelurahan::all();
        $specialists = Specialist::all();
        $item['owner'] = PemilikBengkel::orderBy('name', 'ASC')->get();
        return view('mitra.bengkel.edit', ['bengkel' => $bengkel, 'kecamatans' => $kecamatans, 'kelurahans' => $kelurahans, 'specialists' => $specialists]);
    }

    public function update(Request $request, $id)
    {
        $bengkel = Bengkel::findOrFail($id);

        // Validasi input
        $request->validate([
            'bengkel_name' => 'required|string|max:255',
            'bengkel_description' => 'required|string',
            'bengkel_address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',     // <-- Tambahkan validasi
            'longitude' => 'nullable|numeric|between:-180,180',
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'kelurahan_id' => 'required|exists:kelurahans,id',
            'image' => 'nullable|image|max:2048',
            'specialist_ids' => 'required|array', // Validasi untuk specialist
            'specialist_ids.*' => 'exists:specialists,id', // Validasi setiap ID specialist
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($bengkel->image) {
                File::delete(public_path('images/' . $bengkel->image));
            }

            // Simpan gambar baru
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $bengkel->image = $imageName;
        }

        $owner = Auth::user();
        $owner_id = $owner->id;
        $bengkel->name = $request->bengkel_name;
        $bengkel->description = $request->bengkel_description;
        $bengkel->alamat = $request->bengkel_address;
        $bengkel->pemilik_id = $owner_id;
        $bengkel->kecamatan_id = $request->kecamatan_id;
        $bengkel->kelurahan_id = $request->kelurahan_id;
        $bengkel->latitude = $request->latitude;
        $bengkel->longitude = $request->longitude;
        $bengkel->save();

        // Sinkronisasi relasi dengan specialists
        $bengkel->specialists()->sync($request->specialist_ids);

        return redirect('owner/bengkel')->with('success', 'Bengkel berhasil diperbarui');
    }


    public function destroy($id)
    {
        $data = Bengkel::findOrFail($id);

        if ($data->image) {
            // hapus gambar jika ada
            File::delete(public_path('images/' . $data->image));
        }

        $data->delete();

        return redirect('owner/bengkel')->with('success', 'Bengkel berhasil dihapus');
    }
}

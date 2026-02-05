<?php

namespace App\Http\Controllers;

use App\Models\MerkMobil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class MerkMobilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $merkMobils = MerkMobil::orderBy('created_at', 'desc')->get();
        return view('admin.merk_mobil.index', compact('merkMobils'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.merk_mobil.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_merk' => 'required|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'nullable|string',
        ]);

        $data = $request->only(['nama_merk', 'deskripsi']);

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . $logo->getClientOriginalName();
            $logo->storeAs('public/merk_mobil', $logoName);
            $data['logo'] = $logoName;
        }

        MerkMobil::create($data);

        Alert::success('Berhasil', 'Merk mobil berhasil ditambahkan!');
        return redirect()->route('merk-mobil.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $merkMobil = MerkMobil::with('bengkels')->findOrFail($id);
        return view('admin.merk_mobil.show', compact('merkMobil'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $merkMobil = MerkMobil::findOrFail($id);
        return view('admin.merk_mobil.edit', compact('merkMobil'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_merk' => 'required|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'nullable|string',
        ]);

        $merkMobil = MerkMobil::findOrFail($id);
        $data = $request->only(['nama_merk', 'deskripsi']);

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($merkMobil->logo) {
                Storage::delete('public/merk_mobil/' . $merkMobil->logo);
            }

            $logo = $request->file('logo');
            $logoName = time() . '_' . $logo->getClientOriginalName();
            $logo->storeAs('public/merk_mobil', $logoName);
            $data['logo'] = $logoName;
        }

        $merkMobil->update($data);

        Alert::success('Berhasil', 'Merk mobil berhasil diupdate!');
        return redirect()->route('merk-mobil.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $merkMobil = MerkMobil::findOrFail($id);

        // Hapus logo jika ada
        if ($merkMobil->logo) {
            Storage::delete('public/merk_mobil/' . $merkMobil->logo);
        }

        $merkMobil->delete();

        Alert::success('Berhasil', 'Merk mobil berhasil dihapus!');
        return redirect()->route('merk-mobil.index');
    }
}

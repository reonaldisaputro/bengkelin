<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerkMobil extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_merk',
        'logo',
        'deskripsi',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return url('storage/merk_mobil/' . $this->logo);
        }
        return null;
    }

    public function bengkels()
    {
        return $this->belongsToMany(Bengkel::class, 'bengkel_merk_mobil');
    }
}

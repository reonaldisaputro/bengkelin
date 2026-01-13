<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bengkel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'alamat',
        'latitude',
        'longitude',
        'pemilik_id',
        'kecamatan_id',
        'kelurahan_id',
        // 'specialist_id',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('storage/bengkel/' . $this->image);
        }
        return null;
    }

    public function layanans()
    {
        return $this->hasMany(Layanan::class);
    }

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function pemilik_bengkel()
    {
        return $this->belongsTo(PemilikBengkel::class, 'pemilik_id');
    }

    // Relasi many-to-many dengan Specialist
    public function specialists()
    {
        return $this->belongsToMany(Specialist::class, 'bengkel_specialist');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawRequest::class);
    }
}

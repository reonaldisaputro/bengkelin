<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'user_id',
        'bengkel_id',
        'booking_id',
        'product_id',
        'layanan_id',
        'payment_status',
        'shipping_status',
        'ongkir',
        'administrasi',
        'grand_total',
        "withdrawn_at"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    public function bengkel()
    {
        return $this->belongsTo(Bengkel::class);
    }

    public function detail_transactions()
    {
        return $this->hasMany(DetailTransaction::class);
    }

     public function ratings()
    {
        // jika kolom foreign di Rating mengarah ke transaction_id, relasi langsung bisa:
        return $this->hasMany(Rating::class);
        // alternatif (kalau mau strict via detail): hasManyThrough
        // return $this->hasManyThrough(Rating::class, DetailTransaction::class, 'transaction_id', 'detail_transaction_id', 'id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'layanan_id',
        'product_id',
        'qty',
        'layanan_price',
        'product_price',
    ];

    public function bengkel()
    {
        return $this->belongsTo(Bengkel::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    public function transaction()
    {
        // FK default: transaction_id (sesuai kolom kamu)
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function rating()
    {
        return $this->hasOne(Rating::class, 'detail_transaction_id'); // rating milik user (filter user di query)
    }

    // helper subtotal
    public function getSubtotalAttribute()
    {
        $price = $this->product_price ?? optional($this->product)->price ?? 0;
        return (int)$price * (int)($this->qty ?? 0);
    }
}

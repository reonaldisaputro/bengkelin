<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bengkel_id',
        'category_id',
        'image',
        'description',
        'price',
        'weight',
        'stock'
    ];

    public function bengkel()
    {
        return $this->belongsTo(Bengkel::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // OPSIONAL: ringkasan rating siap pakai
    protected $appends = ['rating_summary', 'image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('images/' . $this->image);
        }
        return null;
    }

    public function getRatingSummaryAttribute()
    {
        return [
            'avg'   => round($this->avg_rating ?? 0, 1),
            'count' => (int) ($this->ratings_count ?? 0),
        ];
    }
}

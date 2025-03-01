<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Review;
use App\Models\Traits\Filterable;

class Product extends Model
{
    use HasFactory, Filterable;

    protected $table = 'products';
    protected $fillable = [
        'title',
        'description',
        'preview_image',
        'price',
        'count',
        'category_id',
        'old_price'
    ];
    protected $casts = [
        'price' => 'float',
        'old_price' => 'float'
    ];

    public function reviews() {
        return $this->hasMany(Review::class, 'product_id', 'id');
    }

    public function getAverageRatingAttribute() {
        $average_rating = $this->reviews()->avg('rating') ?: 0;

        return round($average_rating, 2);
    }

    public function getPreviewImageUrlAttribute() {
        if ($this->preview_image) {
            return url('storage/' . $this->preview_image);
        } else {
            return null;
        }
    }
}

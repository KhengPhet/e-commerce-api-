<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpadSpec extends Model
{
    use HasFactory;
     protected $fillable = [
        'product_id',
        'chipset',
        'ram',
        'storage',
        'display_size',
        'battery',
        'camera_rear',
        'operating_system',
        'network',
        'accessories',
        'weight',
        'warranty',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

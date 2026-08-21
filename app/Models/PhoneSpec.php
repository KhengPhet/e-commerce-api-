<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneSpec extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'display',
        'cpu',
        'ram',
        'storage',
        'battery',
        'camera',
        'operating_system',
        'network',
        'warranty',
    ];

     public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

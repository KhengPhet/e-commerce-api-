<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaptopSpec extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'cpu',
        'ram',
        'storage',
        'screen',
        'vga',
        'os',
        'keyboard',
        'battery',
        'warranty',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

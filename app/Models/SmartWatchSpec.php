<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartWatchSpec extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'display_type',
        'display_size',
        'resolution',
        'chipset',
        'ram',
        'storage',
        'battery_life',
        'charging_type',
        'gps',
        'water_resistance',
        'sensors',
        'connectivity',
        'operating_system',
        'compatibility',
        'weight',
        'warranty',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

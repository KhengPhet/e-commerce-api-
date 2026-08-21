<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function phoneSpec()
    {
        return $this->hasOne(PhoneSpec::class);
    }
    public function laptopSpec()
    {
        return $this->hasOne(LaptopSpec::class);
    }
    public function ipadSpec()
    {
        return $this->hasOne(IpadSpec::class);
    }
    public function smartwatchSpec()
    {
        return $this->hasOne(SmartwatchSpec::class);
    }
}

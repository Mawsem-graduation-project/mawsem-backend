<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'shop_id',
        'sku',
        'name',
        'category_name',
        'unit'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    public function sales(){
        return $this->hasMany(Sale::class);
    }
}

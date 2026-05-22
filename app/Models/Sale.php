<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    //
    protected $fillable = [
        'shop_id',
        'product_id',
        'quantity',
        'sale_date',
    ];
    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function shop(){
        return $this->belongsTo(Shop::class);
    }

}

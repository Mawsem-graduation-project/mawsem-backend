<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    //
    public function products(){
        return $this->belongsTo(Product::class);
    }
    public function shop(){
        return $this->belongsTo(Shop::class);
    }

}

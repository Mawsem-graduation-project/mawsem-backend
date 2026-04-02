<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    //

    protected $fillable = [
        'name',
        'city',
        'address',
    ];

    public function isActive(){
        return $this->is_active;
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function inventories(){
        return $this->hasMany(Inventory::class);
    }

    public function sales(){
        return $this->hasMany(Sale::class);
    }

}

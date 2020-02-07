<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerItem extends Model
{
    protected $guarded = [];

    public function items() {
        return $this->hasMany(\App\Models\Item::class,'id', 'item_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $guarded = [];

    public function item() {
        return $this->hasOne(\App\Models\Item::class, 'item_id');
    }

}

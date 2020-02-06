<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $guarded = [];

    public function bank_transaction()
    {
        return $this->hasMany(\App\Models\BankTransaction::class, 'id', 'bank_trans_id');
    }

    public function items()
    {
        return $this->hasMany(\App\DR_ITEM::class, 'transaction_id');
    }
}

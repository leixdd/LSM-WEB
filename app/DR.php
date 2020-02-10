<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\BankTransaction;

class DR extends Model
{
    protected $table = 'deliveryreciepts';
    protected $guarded = [];

    
    public function bank_transactions() {
        return $this->belongsToMany(BankTransaction::class)->withTimestamps();
    }

    public function billing_items() {
        return $this->hasMany(\App\Models\Billing::class, 'dr_trans_no');
    }
}

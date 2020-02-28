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

    public function billed_items() {
        return $this->hasMany(\App\Models\Billing::class, 'dr_trans_no')->where('isReturned', 0);
    }

    public function customer() {
        return $this->hasOne(\App\Customer::class, 'id', 'delivered_to');
    }

    public function user() {
        return $this->hasOne(\App\User::class, 'id', 'updated_by');
    }
}

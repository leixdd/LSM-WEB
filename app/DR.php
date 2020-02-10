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
}

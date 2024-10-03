<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletWithdraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_holder_name',
        'bank_name',
        'branch_name',
        'account_number',
        'account_type',
        'ifsc_code',
        'amount',
        'status',
        'reject_reason'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'user_id');
    }
}

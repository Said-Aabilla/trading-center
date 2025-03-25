<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'symbol',
        'quantity',
        'sector',
        'portfolio_id',
        'current_price',
        'cout_moyen'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
   
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccurateCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'client_secret',
        'redirect_uri',
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

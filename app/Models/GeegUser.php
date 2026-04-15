<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeegUser extends Model
{
    protected $table = 'geeg_user';
    protected $fillable = [
        'geeg_id',
        'user_id',
    ];
    public function geeg()
    {
        return $this->belongsTo(Geeg::class, 'geeg_id');
    }
     public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //
}

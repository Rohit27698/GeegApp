<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Geeg extends Model
{
    protected $table = 'geeg';

    protected $fillable = [
        'title',
        'discription',
        'subject',
        'created_by',
        'status',
        'deadline',
        'image',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function applicants()
    {
        return $this->belongsToMany(User::class, 'geeg_applications');
    }
}

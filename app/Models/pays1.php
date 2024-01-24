<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pays1 extends Model
{
    protected $table = 'pays1'; 
    protected $fillable = [
        'email','ref', 'name', 'time', 'event'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pays0 extends Model
{
    protected $table = 'pays0'; 
    protected $fillable = [
        'diocese_id','ref', 'name', 'time', 'year','amt'
    ];
}

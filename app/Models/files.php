<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class files extends Model
{
    protected $table = 'files'; 
    protected $fillable = [
        'diocese_id','folder', 'file'
    ];
}

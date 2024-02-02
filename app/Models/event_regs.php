<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class event_regs extends Model
{
    protected $table = 'event_regs'; 
    protected $fillable = [
        'event_id','diocese_id', 'proof', 'verif'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

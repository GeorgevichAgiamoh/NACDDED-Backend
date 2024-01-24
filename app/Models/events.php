<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class events extends Model
{
    protected $table = 'events'; 
    protected $fillable = [
        'title','time', 'venue', 'fee','start','end','theme'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

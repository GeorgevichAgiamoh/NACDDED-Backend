<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class diocese_general_data extends Model
{
    protected $table = 'diocese_general_data';
    protected $primaryKey = 'email';
    protected $fillable = [
         'diocese_id','state','lga', 'addr'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

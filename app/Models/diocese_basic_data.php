<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class diocese_basic_data extends Model
{
    protected $table = 'diocese_basic_data';
    protected $primaryKey = 'diocese_id';
    protected $fillable = [
        'diocese_id', 'name', 'phn', 'verif'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

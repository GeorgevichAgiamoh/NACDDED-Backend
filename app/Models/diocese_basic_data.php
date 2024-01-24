<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class diocese_basic_data extends Model
{
    protected $table = 'diocese_basic_data';
    protected $primaryKey = 'email';
    protected $fillable = [
        'email', 'name', 'phn', 'pwd'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

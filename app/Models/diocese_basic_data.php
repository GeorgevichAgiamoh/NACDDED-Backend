<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class diocese_basic_data extends Model
{
    protected $table = 'diocese_basic_data';
    protected $primaryKey = 'diocese_id';
    public $incrementing = false;
    protected $fillable = [
        'name', 'phn', 'pwd', 'verif'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

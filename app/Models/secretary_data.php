<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class secretary_data extends Model
{
    protected $table = 'secretary_data'; 
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $fillable = [
        'email', 'fname', 'mname', 'lname', 'sex','phn', 'addr','diocese_id'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

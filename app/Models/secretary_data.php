<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class secretary_data extends Model
{
    protected $table = 'secretary_data'; 
    protected $primaryKey = 'email';
    protected $fillable = [
        'email', 'fname', 'mname', 'lname', 'sex','phn', 'addr'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}
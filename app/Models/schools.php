<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class schools extends Model
{
    protected $table = 'schools'; 
    protected $fillable = [
        'diocese_id', 'name', 'type', 'lea','addr','email','phone','p_name','p_email','p_phone'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

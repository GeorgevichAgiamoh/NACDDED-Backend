<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class nacdded_info extends Model
{
    protected $table = 'nacdded_info'; 
    public $incrementing = false;
    protected $primaryKey = 'email';
    protected $fillable = [
        'email','cname', 'regno', 'addr','nationality', 'state','lga','aname', 'anum','bnk','pname','peml','pphn','paddr'
    ];
    /*protected $hidden = [
        'password',
    ];*/
}

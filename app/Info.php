<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    //
    protected $table = 'user_infoes';
    protected $primaryKey = 'user_id';
    protected $fillable = ['user_id','name','sex','address','about_me'];
 	
        public function user()
    {
        return $this->belongsTo('App\User');
    }
}

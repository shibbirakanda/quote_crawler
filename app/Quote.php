<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    //
    protected $fillable = ['title','author'];

    public function tags()
	{
		return $this->hasMany('App\Tag');
    }
}

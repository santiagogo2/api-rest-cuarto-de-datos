<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $table = 'folder';

	public function documents(){
		return $this->hasMany('App\Documents');
	}

    protected $fillable = [
    	'name',
    ];
}

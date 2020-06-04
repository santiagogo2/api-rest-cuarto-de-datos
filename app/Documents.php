<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
	protected $table = 'documents';

	public function user(){
		return $this->belongsTo('App\User', 'user_id');
	}

    protected $fillable = [
    	'name', 'document_name', 'user_id',
    ];

}

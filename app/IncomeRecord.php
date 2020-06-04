<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncomeRecord extends Model
{
    protected $table = 'income_record';

    protected $fillable  = [
    	'user', 'document_name', 
    ];
}

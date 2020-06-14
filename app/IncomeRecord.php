<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncomeRecord extends Model
{
    protected $table = 'income_records';

    protected $fillable  = [
    	'user', 'document_name', 
    ];
}

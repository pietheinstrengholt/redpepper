<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['department_name','department_description'];
    protected $guarded = [];
	protected $table = 't_departments';
}

?>

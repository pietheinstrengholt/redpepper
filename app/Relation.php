<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
	protected $fillable = ['relation_name','relation_color','relation_description','created_by'];
	protected $guarded = [];
	protected $table = 't_bim_relation_types';
}

?>
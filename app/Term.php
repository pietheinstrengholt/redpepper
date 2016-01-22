<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
	protected $fillable = ['term_name','term_definition'];
	protected $guarded = [];
	protected $table = 't_bim_terms';
}

?>
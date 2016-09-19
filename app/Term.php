<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
	protected $fillable = ['glossary_id','status_id','owner_id','term_name','term_description'];
	protected $guarded = [];
	protected $table = 't_terms';

	public function owner()
	{
		return $this->belongsTo('App\User','owner_id','id');
	}
}

?>

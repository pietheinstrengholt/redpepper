<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
	protected $fillable = ['glossary_id','status_id','owner_id','term_name','term_description'];
	protected $guarded = [];
	protected $table = 't_bim_terms';

	public function glossary()
	{
		return $this->belongsTo('App\Glossary');
	}

	public function status()
	{
		return $this->belongsTo('App\Status','status_id','id');
	}

	public function owner()
	{
		return $this->belongsTo('App\User','owner_id','id');
	}

	public function objects()
	{
		return $this->hasMany('App\Ontology','subject_id','id');
	}
}



?>
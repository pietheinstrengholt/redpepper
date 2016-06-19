<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ontology extends Model
{
	protected $fillable = ['subject_id','status_id','relation_id','object_id','created_by'];
	protected $guarded = [];
	protected $table = 't_bim_ontology';

	public function subject()
	{
		return $this->belongsTo('App\Term','subject_id','id');
	}

	public function object()
	{
		return $this->belongsTo('App\Term','object_id','id');
	}

	public function status()
	{
		return $this->belongsTo('App\Status','status_id','id');
	}

	public function relation()
	{
		return $this->belongsTo('App\Relation','relation_id','id');
	}
}

?>
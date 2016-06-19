<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Glossary extends Model
{
	protected $fillable = ['glossary_name','status_id','glossary_description','created_by'];
	protected $guarded = [];
	protected $table = 't_bim_glossaries';

	public function terms()
	{
		return $this->hasMany('App\Term');
	}

	public function status()
	{
		return $this->belongsTo('App\Status','status_id','id');
	}
}

?>
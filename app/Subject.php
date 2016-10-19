<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
	protected $fillable = ['subject_name','parent_id','subject_description','subject_longdesc','visible','subject_order'];
	protected $guarded = [];
	protected $table = 't_subjects';

	public function sections()
	{
		return $this->hasMany('App\Section', 'subject_id', 'id');
	}

	public function parent()
	{
		return $this->belongsTo('App\Subject', 'parent_id');
	}

	public function children()
	{
		return $this->hasMany('App\Subject', 'parent_id');
	}
}

?>

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
	protected $fillable = ['subject_name','subject_description','subject_longdesc','visible','subject_order'];
	protected $guarded = [];
	protected $table = 't_subjects';

	public function sections()
	{
		return $this->hasMany('App\Section', 'subject_id', 'id');
	}
}

?>

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
	protected $fillable = ['description','created_by'];
	protected $guarded = [];
	protected $table = 't_activity_log';

	public function creator()
	{
		return $this->belongsTo('App\User', 'created_by', 'id');
	}
}

?>

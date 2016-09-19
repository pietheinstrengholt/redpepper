<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
	protected $fillable = ['log_event','action','changerequest_id','section_id','template_id','username_id','created_by'];
	protected $guarded = [];
	protected $table = 't_logs';

	public function creator()
	{
		return $this->belongsTo('App\User', 'created_by', 'id');
	}

	public function user()
	{
		return $this->belongsTo('App\User', 'username_id', 'id');
	}

	public function section()
	{
		return $this->belongsTo('App\Section', 'section_id', 'id');
	}

	public function template()
	{
		return $this->belongsTo('App\Template', 'template_id', 'id');
	}
}

?>

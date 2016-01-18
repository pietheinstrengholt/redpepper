<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftTechnical extends Model
{
	protected $fillable = ['changerequest_id','template_id','row_code','column_code','source_id','type_id','content','description'];
	protected $guarded = [];
	protected $table = 't_changes_technical';

	public function changerequest()
	{
		return $this->belongsTo('App\ChangeRequest');
	}

	public function type()
	{
		return $this->hasOne('App\TechnicalType', 'id', 'type_id');
	}

	public function source()
	{
		return $this->hasOne('App\TechnicalSource', 'id', 'source_id');
	}
}

?>

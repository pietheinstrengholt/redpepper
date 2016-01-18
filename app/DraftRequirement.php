<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftRequirement extends Model
{
	protected $fillable = ['changerequest_id','template_id','row_code','column_code','content_type','content'];
	protected $guarded = [];
	protected $table = 't_changes_content';

	public function changerequest()
	{
		return $this->belongsTo('App\ChangeRequest');
	}

}

?>

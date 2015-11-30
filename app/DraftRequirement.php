<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftRequirement extends Model
{
    protected $fillable = ['changerequest_id','field_id','content_type','content'];
    protected $guarded = [];
	protected $table = 't_draft_requirements';
	
    public function changerequest()
    {
        return $this->belongsTo('App\ChangeRequest');
    }	
	
}

?>

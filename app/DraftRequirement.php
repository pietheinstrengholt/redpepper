<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftRequirement extends Model
{
    protected $fillable = ['changerequest_id','row_legal_desc','row_interpretation_desc','column_legal_desc','column_interpretation_desc'];
    protected $guarded = [];
	protected $table = 't_draft_requirements';
	
    public function changerequest()
    {
        return $this->belongsTo('App\ChangeRequest');
    }	
	
}

?>

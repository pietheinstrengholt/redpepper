<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftTechnical extends Model
{
    protected $fillable = ['changerequest_id','source_id','type_id','content','description'];
    protected $guarded = [];
	protected $table = 't_draft_technical_info';
	
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
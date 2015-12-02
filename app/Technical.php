<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Technical extends Model
{
    protected $fillable = ['template_id','source_id','type_id','content','row_num','column_num','content','description'];
    protected $guarded = [];
	protected $table = 't_technical_info';

    public function type()
    {
		return $this->hasOne('App\TechnicalType', 'id', 'type_id');
    }
	
    public function source()
    {
		return $this->hasOne('App\TechnicalSource', 'id', 'source_id');
    }
	
    public function template()
    {
        return $this->belongsTo('App\Template');
    }	
}

?>

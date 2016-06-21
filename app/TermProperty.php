<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TermProperty extends Model
{
	protected $fillable = ['term_id','property_name','property_value','created_by'];
	protected $guarded = [];
	protected $table = 't_bim_terms_properties';

	public function term()
	{
		return $this->belongsTo('App\Term');
	}
}



?>
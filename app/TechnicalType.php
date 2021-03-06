<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TechnicalType extends Model
{
	protected $fillable = ['type_name','type_description'];
	protected $guarded = [];
	protected $table = 't_technical_types';

	public function descriptions()
	{
		return $this->hasMany('App\TechnicalDescription', 'type_id', 'id');
	}

}

?>

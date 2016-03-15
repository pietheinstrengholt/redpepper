<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TechnicalDescription extends Model
{
	protected $fillable = ['type_id','content','description'];
	protected $guarded = [];
	protected $table = 't_technical_descriptions';
}

?>

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TechnicalType extends Model
{
    protected $fillable = ['type_name','type_description'];
    protected $guarded = [];
	protected $table = 't_technical_info_type';
}

?>

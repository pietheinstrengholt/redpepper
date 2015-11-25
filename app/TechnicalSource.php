<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TechnicalSource extends Model
{
    protected $fillable = ['source_name','source_description'];
    protected $guarded = [];
	protected $table = 't_technical_info_source';
}

?>

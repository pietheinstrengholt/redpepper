<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $fillable = ['template_id','field_id','reference','legal_desc','interpretation_desc'];
    protected $guarded = [];
	protected $table = 't_requirements';

    public function template()
    {
        return $this->belongsTo('App\Template');
    }
}

?>

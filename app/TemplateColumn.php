<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemplateColumn extends Model
{
    protected $fillable = ['template_id','column_num','column_code','column_description','column_property'];
    protected $guarded = [];
	protected $table = 't_template_columns';

    public function template()
    {
        return $this->belongsTo('App\Template');
    }
}

?>

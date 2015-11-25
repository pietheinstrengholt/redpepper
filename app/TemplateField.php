<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    protected $fillable = ['template_id','row_name','column_name','property','content'];
    protected $guarded = [];
	protected $table = 't_template_field_property';

    public function template()
    {
        return $this->belongsTo('App\Template');
    }
}

?>

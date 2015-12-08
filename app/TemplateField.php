<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    protected $fillable = ['template_id','row_code','column_code','property','content'];
    protected $guarded = [];
	protected $table = 't_template_cells';

    public function template()
    {
        return $this->belongsTo('App\Template');
    }
}

?>

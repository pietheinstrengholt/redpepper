<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemplateRow extends Model
{
  protected $fillable = ['template_id','row_num','row_code','row_description','row_property','row_reference'];
  protected $guarded = [];
  protected $table = 't_template_rows';

  public function template()
  {
    return $this->belongsTo('App\Template');
  }
}

?>

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryTechnical extends Model
{
  protected $fillable = ['changerequest_id','template_id','row_code','column_code','source_id','type_id','content','description','change_type','submission_date','approved_by','created_by'];
  protected $guarded = [];
  protected $table = 't_history_technical';

  public function changerequest()
  {
    return $this->belongsTo('App\ChangeRequest');
  }
}

?>

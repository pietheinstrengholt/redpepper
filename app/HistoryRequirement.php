<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryRequirement extends Model
{
    protected $fillable = ['changerequest_id','template_id','row_code','column_code','content_type','content','change_type','submission_date','approved_by','created_by'];
    protected $guarded = [];
	protected $table = 't_history_content';

    public function changerequest()
    {
        return $this->belongsTo('App\ChangeRequest');
    }
}

?>

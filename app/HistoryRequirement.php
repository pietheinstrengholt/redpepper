<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryRequirement extends Model
{
    protected $fillable = ['changerequest_id','template_id','row_name','column_name','content_type','content','change_type','submission_date','approved_by','created_by'];
    protected $guarded = [];
	protected $table = 't_content_history';

    public function changerequest()
    {
        return $this->belongsTo('App\ChangeRequest');
    }
}

?>

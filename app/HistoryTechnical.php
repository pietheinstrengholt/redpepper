<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryTechnical extends Model
{
    protected $fillable = ['changerequest_id','template_id','row_name','column_name','source_id','type_id','content','description','change_type','submission_date','approved_by','created_by'];
    protected $guarded = [];
	protected $table = 't_technical_info_history';

    public function changerequest()
    {
        return $this->belongsTo('App\ChangeRequest');
    }
}

?>

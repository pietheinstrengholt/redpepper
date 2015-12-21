<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = ['content_type','content_action','content_name','created_by'];
    protected $guarded = [];
	protected $table = 't_logs';

    public function user()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}

?>

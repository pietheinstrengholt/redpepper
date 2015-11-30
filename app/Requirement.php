<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $fillable = ['template_id','field_id','content_type','content'];
    protected $guarded = [];
	protected $table = 't_content';

    public function template()
    {
        return $this->belongsTo('App\Template');
    }
}

?>

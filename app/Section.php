<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['section_name','section_description','section_longdesc','visible','reporting_frequency'];
	protected $guarded = [];
    protected $table = 't_sections';

    public function templates()
    {
        return $this->hasMany('App\Template');
    }
}

?>

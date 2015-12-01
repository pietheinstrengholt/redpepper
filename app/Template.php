<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['section_id','template_name','template_shortdesc','template_longdesc','frequency_description','reporting_dates_description','main_changes_description','links_other_temp_description','process_and_organisation_description','type_id','sortorder','visible'];
    protected $guarded = [];
	protected $table = 't_templates';

    public function section()
    {
        return $this->belongsTo('App\Section');
    }
	
    public function rows()
    {
        return $this->hasMany('App\TemplateRow');
    }
	
    public function columns()
    {
        return $this->hasMany('App\TemplateColumn');
    }
	
    public function fields()
    {
        return $this->hasMany('App\TemplateField');
    }
	
    public function requirement()
    {
        return $this->hasMany('App\Requirement');
    }
	
}

?>

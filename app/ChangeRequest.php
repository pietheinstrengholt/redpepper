<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
	protected $fillable = ['template_id','row_code','column_code','creator_id','approver_id','status','comment'];
	protected $guarded = [];
	protected $table = 't_changes';

	public function drafttechnical()
	{
		return $this->belongsTo('App\DraftTechnical');
	}

	public function draftrequirement()
	{
		return $this->hasMany('App\DraftRequirement');
	}

	public function draftfield()
	{
		return $this->hasMany('App\DraftField');
	}

	public function template()
	{
		return $this->hasOne('App\Template', 'id', 'template_id');
	}

	public function creator()
	{
		return $this->hasOne('App\User', 'id', 'creator_id');
	}

	public function approver()
	{
		return $this->hasOne('App\User', 'id', 'approver_id');
	}
}

?>

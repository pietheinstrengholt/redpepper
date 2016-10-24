<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRights extends Model
{
	protected $fillable = ['username_id','section_id','subject_id'];
	protected $guarded = [];
	protected $table = 't_usernames_rights';

	public function user()
	{
		return $this->belongsTo('App\User');
	}
}

?>

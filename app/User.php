<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword;

	/**
	* It looks like user table is defined in 3 different place out of the box Laravel 5.
	* 1) /config/auth.php ('table' => 'users',)
	* 2) In User.php (protected $table)
	* 3) In AuthController.php (there is a unique field validator as in @Kollley reply )
	*/
	protected $table = 't_usernames';
	protected $fillable = ['username', 'email', 'password', 'section_id', 'entity_id', 'role', 'firstname', 'lastname', 'department_id'];
	protected $hidden = ['password', 'remember_token'];

	public function department()
	{
		return $this->hasOne('App\Department', 'id', 'department_id');
	}

	public function changerequest()
	{
		return $this->hasMany('App\ChangeRequest');
	}

	public function rights()
	{
		return $this->hasMany('App\UserRights', 'id', 'username_id');
	}

	public function sections()
    {
        return $this->belongsToMany('App\Section', 't_usernames_rights', 'username_id', 'section_id');
    }

	public function subjects()
    {
        return $this->belongsToMany('App\Subject', 't_usernames_rights', 'username_id', 'subject_id');
    }
}

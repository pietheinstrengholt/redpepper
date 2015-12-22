<?php

namespace App\Http\Controllers\Auth;
use App\Department;
use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

	/**
	* User model instance
	* @var User
	*/
	protected $user;

	/**
	* For Guard
	*
	* @var Authenticator
	*/
	protected $auth;

	use AuthenticatesAndRegistersUsers, ThrottlesLogins;

	protected $redirectPath = '/';

	protected $loginPath = '/public/auth/login';

	/**
	* Create a new authentication controller instance.
	*
	* @return void
	*/
	public function __construct(Guard $auth, User $user)
	{
		$this->user = $user;
		$this->auth = $auth;
		$this->middleware('guest', ['except' => 'getLogout']);
	}

	/**
	* Get a validator for an incoming registration request.
	*
	* @param  array  $data
	* @return \Illuminate\Contracts\Validation\Validator
	*/
	protected function validator(array $data)
	{
		return Validator::make($data, [
			'username' => 'required|min:4|max:255|unique:t_usernames',
			'firstname' => 'required|max:255',
			'lastname' => 'required|max:255',
			'department_id' => 'required',
			//'email' => 'required|email|max:255|unique:t_usernames',
			'password' => 'required|confirmed|min:6',
		]);
	}

	/**
	* Create a new user instance after a valid registration.
	*
	* @param  array  $data
	* @return User
	*/
	protected function create(array $data)
	{
		return User::create([
			'username' => $data['username'],
			'firstname' => $data['firstname'],
			'lastname' => $data['lastname'],
			'department_id' => $data['department_id'],
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
			//'password' => md5($data['password']),
		]);
	}

	/* Login get post methods */
	protected function getRegister() {
		$departments = Department::orderBy('department_name', 'asc')->get();
		return view('auth.register', compact('departments','user'));
	}

	public function authenticate()
	{
		if (Auth::attempt(['username' => $username, 'password' => $password])) {
			// Authentication passed...
			return redirect('/');
		}
	}

	protected function postLogin(LoginRequest $request)
	{
		if ($this->auth->attempt($request->only('username', 'password'))) {
		return redirect('/');
	}

	return redirect('auth/login')->withErrors([
		'username' => 'The username or the password is invalid. Please try again.',
		]);
	}

}

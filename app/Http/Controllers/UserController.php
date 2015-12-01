<?php

namespace App\Http\Controllers;
use DB;
use App\User;
use App\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Input;
use Redirect;

class UserController extends Controller
{

    public function index()
    {
		$users = User::all();
		return view('users.index', compact('users'));
    }
	
	public function edit(User $user)
	{
		$departments = Department::orderBy('department_name', 'asc')->get();
		return view('users.edit', compact('departments','user'));
	}
 
	public function update(User $user)
	{
		$input = array_except(Input::all(), '_method');
		$user->update($input);
		return Redirect::route('users.show', $user->slug)->with('message', 'User updated.');
	}
	 
	public function destroy(User $user)
	{
		$user->delete();
		return Redirect::route('users.index')->with('message', 'User deleted.');
	}
	
}

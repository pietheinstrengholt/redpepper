<?php

namespace App\Http\Controllers;
use DB;
use App\User;
use App\UserRights;
use App\Section;
use Gate;
use App\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Input;
use Redirect;

class UserController extends Controller
{
    public function index()
    {
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
	
		$users = User::orderBy('username', 'asc')->get();
		return view('users.index', compact('users'));
    }
	
	public function edit(User $user)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
	
		$departments = Department::orderBy('department_name', 'asc')->get();
		return view('users.edit', compact('departments','user'));
	}

	public function rights($id)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
		
		$roles = array(
			"superadmin" => "superadmin",
			"admin" => "admin",
			"contributor" => "contributor",
			"reviewer" => "reviewer",
			"builder" => "builder",
			"guest" => "guest",
		);
		
		$user = User::find($id);
		$userrights = UserRights::where('username_id', $id)->get();
		
		$sectionrights = array();
		$userrights = $userrights->toArray();
		if (!empty($userrights)) {
			foreach ($userrights as $userright) {
				array_push($sectionrights,$userright['section_id']);
			}
		}
		
		$sections = Section::orderBy('section_name', 'asc')->get();
		return view('users.editrights', compact('user', 'roles', 'sections', 'sectionrights'));
	}	
 
	public function update(User $user)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
	
		$input = array_except(Input::all(), '_method');
		$user->update($input);
		return Redirect::route('users.show', $user->slug)->with('message', 'User updated.');
	}
	
	public function updaterights(Request $request)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
		
		if ($request->isMethod('post')) {
			if ($request->has('username_id')) {
				if ($request->has('role')) {
					User::where('id', $request->input('username_id'))->update(['role' => $request->input('role')]);
				}
			}
			
			UserRights::where('username_id', $request->input('username_id'))->delete();
			
			if ($request->has('section')) {
				
				foreach($request->input('section') as $key => $value) {
					//create new rights
					$UserRights = new UserRights;
					$UserRights->username_id = $request->input('username_id');
					$UserRights->section_id = $value;
					$UserRights->save();
				}
			}
		
		}
		
		return Redirect::route('users.index')->with('message', 'User updated.');
	}	
	 
	public function destroy(User $user)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
	
		$user->delete();
		return Redirect::route('users.index')->with('message', 'User deleted.');
	}
	
}

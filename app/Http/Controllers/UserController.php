<?php

namespace App\Http\Controllers;
use App\Activity;
use App\Department;
use App\Helpers\ActivityLog;
use App\Http\Controllers\Controller;
use App\Log;
use App\Section;
use App\Subject;
use App\User;
use App\UserRights;
use Auth;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class UserController extends Controller
{
    public function index()
    {
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$users = User::with('department')->orderBy('username', 'asc')->get();
		return view('users.index', compact('users'));
    }

	public function edit(User $user)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$user->id) {
			abort(403, 'This user no longer exists in the database.');
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
			/* "admin" => "admin", */
			"contributor" => "contributor",
			/* "reviewer" => "reviewer", */
			"builder" => "builder",
			"guest" => "guest",
		);

		$user = User::findOrFail($id);
		$userrights = UserRights::where('username_id', $id)->get();

		$sectionrights = array();
		$subjectrights = array();

		$userrights = $userrights->toArray();
		if (!empty($userrights)) {
			foreach ($userrights as $userright) {
				if (isset($userright['section_id'])) {
					array_push($sectionrights,$userright['section_id']);
				}
				if (isset($userright['subject_id'])) {
					array_push($subjectrights,$userright['subject_id']);
				}
			}
		}

		$subjects = Subject::orderBy('subject_name', 'asc')->get();
		return view('users.editrights', compact('user', 'roles', 'subjects', 'sectionrights', 'subjectrights'));
	}

	public function password($id)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$user = User::findOrFail($id);
		return view('users.editpassword', compact('user'));
	}

	public function updatepassword(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'password' => 'required|confirmed|min:6',
		]);

		if ($request->isMethod('post')) {

			//find user
			$user = User::findOrFail($request->input('username_id'));

			//update password
			User::where('id', $request->input('username_id'))->update(['password' => bcrypt($request->input('password'))]);

			//log activity
			ActivityLog::submit("User " . $user->username . " was updated.");
		}
		//return to user overview
		return Redirect::route('users.index')->with('message', 'Password updated.');
	}

	public function update(User $user, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'firstname' => 'required',
			'lastname' => 'required',
			'email' => 'required|email',
			'department_id' => 'required'
		]);

		$user->update($request->all());

		//log activity
		ActivityLog::submit("User " . $user->username . " was updated.");

		return Redirect::route('users.index')->with('message', 'User updated.');
	}

	public function updaterights(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'username_id' => 'required',
			'role' => 'required'
		]);

		if ($request->isMethod('post')) {

			User::where('id', $request->input('username_id'))->update(['role' => $request->input('role')]);
			UserRights::where('username_id', $request->input('username_id'))->delete();

			//add rights for selected sections
			if ($request->has('section')) {
				foreach($request->input('section') as $key => $value) {
					$UserRights = new UserRights;
					$UserRights->username_id = $request->input('username_id');
					$UserRights->section_id = $value;
					$UserRights->save();
				}
			}

			//add rights for selected subjects
			if ($request->has('subject')) {
				foreach($request->input('subject') as $key => $value) {
					$UserRights = new UserRights;
					$UserRights->username_id = $request->input('username_id');
					$UserRights->subject_id = $value;
					$UserRights->save();
				}
			}

		}

		//find user
		$user = User::findOrFail($request->input('username_id'));

		//log activity
		ActivityLog::submit("Userrights for " . $user->username . " were updated.");

		return Redirect::route('users.index')->with('message', 'User updated.');
	}

	public function destroy(User $user)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//delete logs
		Log::where('created_by', $user->id)->delete();

		//find user
		$user = User::findOrFail($user->id);

		//log activity
		ActivityLog::submit("User " . $user->username . " was deleted.");

		//delete user
		$user->delete();

		return Redirect::route('users.index')->with('message', 'User deleted.');
	}

	public function show(User $user)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		$user->load('activities');
		return view('users.show', compact('user'));
	}

}

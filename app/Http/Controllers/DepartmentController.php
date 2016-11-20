<?php

namespace App\Http\Controllers;
use App\Department;
use App\Helpers\ActivityLog;
use App\Http\Controllers\Controller;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class DepartmentController extends Controller
{
    public function index()
    {
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

  		$departments = Department::orderBy('department_name', 'asc')->get();
  		return view('departments.index', compact('departments'));
    }

	public function edit(Department $department)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$department->id) {
			abort(403, 'This department no longer exists in the database.');
		}

		return view('departments.edit', compact('department'));
	}

	public function create(Department $department)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		return view('departments.create', compact('department'));
	}

	public function store(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'department_name' => 'required|min:3|unique:t_departments',
			'department_description' => 'required'
		]);

		Department::create($request->all());

		return Redirect::route('departments.index')->with('message', 'Department created');
	}

	public function update(Department $department, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'department_name' => 'required|min:3',
			'department_description' => 'required'
		]);

		$department->update($request->all());

		//Log activity
		ActivityLog::submit("Department " . $department->department_name . " was updated.");

		return Redirect::route('departments.show', $department->slug)->with('message', 'Department updated.');
	}

	public function destroy(Department $department)
	{
 		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//Log activity
		ActivityLog::submit("Department " . $department->department_name . " was deleted.");

		$department->delete();
		return Redirect::route('departments.index')->with('message', 'Department deleted.');
	}

}

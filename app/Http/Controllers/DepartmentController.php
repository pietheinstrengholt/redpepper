<?php

namespace App\Http\Controllers;
use DB;
use App\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Input;
use Redirect;

class DepartmentController extends Controller
{

    public function index()
    {
		$departments = Department::all();
		return view('departments.index', compact('departments'));
    }
	
	public function edit(Department $department)
	{
		return view('departments.edit', compact('department'));
	}
	
	public function create(Department $department)
	{
		return view('departments.create', compact('department'));
	}	

	public function store()
	{
		$input = Input::all();
		Department::create( $input );
		return Redirect::route('departments.index')->with('message', 'Department created');
	}
	 
	public function update(Department $department)
	{
		$input = array_except(Input::all(), '_method');
		$department->update($input);
		return Redirect::route('departments.show', $department->slug)->with('message', 'Department updated.');
	}
	 
	public function destroy(Department $department)
	{
		$department->delete();
		return Redirect::route('departments.index')->with('message', 'Department deleted.');
	}
	
}

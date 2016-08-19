<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Subject;
use App\User;
use App\UserRights;
use Auth;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class SubjectController extends Controller
{
	public function index(Request $request)
	{
		$subjects = Subject::orderBy('subject_name', 'asc')->get();

		//abort if sectionRights array is empty
		if (empty($subjects)) {
			abort(403, 'No subjects have been found. Please ask your administrator to add any subjects.');
		}

		return view('subjects.index', compact('subjects'));
	}

	public function edit(Request $request, Subject $subject)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$subject->id) {
			abort(403, 'This subject no longer exists in the database.');
		}

		return view('subjects.edit', compact('subject'));
	}

	public function create(Request $request, Subject $subject)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		return view('subjects.create', compact('subject'));
	}

	public function store(Request $request)
	{
		//only a superadmin has permissions to create new sections
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'subject_name' => 'required|min:4',
			'subject_description' => 'required|min:4'
		]);

		$subject = Subject::create($request->all());

		return Redirect::route('subjects.index')->with('message', 'Subject created');
	}

	public function update(Request $request, Subject $subject)
	{
		//only a superadmin has permissions to create new sections
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'subject_name' => 'required|min:4',
			'subject_description' => 'required|min:4'
		]);

		$subject->update($request->all());

		return Redirect::route('subjects.index')->with('message', 'Subject updated.');
	}

	public function destroy(Subject $subject)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$subject->delete();
		return Redirect::route('subjects.index')->with('message', 'Subject deleted.');
	}
}

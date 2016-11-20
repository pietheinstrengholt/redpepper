<?php

namespace App\Http\Controllers;
use App\Helpers\ActivityLog;
use App\Http\Controllers\Controller;
use App\Section;
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

	public function show(Subject $subject)
	{
		//only non guests can see all sections
		if (Gate::allows('see-nonvisible-content')) {
			$sections = Section::with('subject')->orderBy('section_name', 'asc')->where('subject_id', $subject->id)->get();
		} else {
			$sections = Section::with('subject')->orderBy('section_name', 'asc')->where('subject_id', $subject->id)->where('visible', '<>' , 'False')->get();
		}

		//sort sections on natural ordering
		$sections = $sections->sortBy('section_name', SORT_NATURAL);

		return view('subjects.show', compact('subject','sections'));
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

		$subjects = Subject::orderBy('subject_name', 'asc')->where('id', '!=', $subject->id)->get();

		return view('subjects.edit', compact('subject','subjects'));
	}

	public function create(Request $request, Subject $subject)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$subjects = Subject::orderBy('subject_name', 'asc')->get();

		return view('subjects.create', compact('subject','subjects'));
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

		//Log activity
		ActivityLog::submit("Subject " . $subject->subject_name . " was created.");

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

		//Log activity
		ActivityLog::submit("Subject " . $subject->subject_name . " was updated.");

		return Redirect::route('subjects.index')->with('message', 'Subject updated.');
	}

	public function destroy(Subject $subject)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//Log activity
		ActivityLog::submit("Subject " . $subject->subject_name . " was deleted.");

		$subject->delete();
		return Redirect::route('subjects.index')->with('message', 'Subject deleted.');
	}
}

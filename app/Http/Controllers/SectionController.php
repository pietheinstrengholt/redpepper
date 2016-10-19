<?php

namespace App\Http\Controllers;
use App\Events\SectionCreated;
use App\Events\SectionUpdated;
use App\Events\SectionDeleted;
use App\Http\Controllers\Controller;
use App\Section;
use App\Subject;
use App\Template;
use App\User;
use App\UserRights;
use App\FileUpload;
use Auth;
use Event;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class SectionController extends Controller
{
	public function show(Section $section, Subject $subject)
	{
		//check if id property exists
		if (!$section->id) {
			abort(403, 'This section no longer exists in the database.');
		}

		//also show files overview on the section index page
		$files = FileUpload::orderBy('file_name', 'asc')->where('section_id', $section->id)->get();

		//check if visible is set to false and user is a guest
		if (Gate::denies('see-nonvisible-content') && $section->visible == "False") {
			abort(403, 'Unauthorized action.');
		}

		//only non guests will see the hidden templates
		if (Gate::allows('see-nonvisible-content')) {
			$templates = Template::orderBy('template_name', 'asc')->where('section_id', $section->id)->where('parent_id', null)->orWhere(function ($query) use ($section) {
				$query->where('section_id', $section->id)->where('parent_id', 0);
			})->get();
		} else {
			$templates = Template::orderBy('template_name', 'asc')->where('section_id', $section->id)->where('visible', '<>' , 'False')->where('parent_id', null)->orWhere(function ($query) use ($section) {
				$query->where('section_id', $section->id)->where('visible', '<>' , 'False')->where('parent_id', 0);
			})->get();
		}

		//sort templates on natural ordering
		$templates = $templates->sortBy('template_name', SORT_NATURAL);

		return view('sections.show', compact('section', 'templates', 'files'));
	}

	public function edit(Request $request, Section $section, Subject $subject)
	{
		if (Auth::guest()) {
			abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$section->id) {
			abort(403, 'This section no longer exists in the database.');
		}

		//retrieve subjects for dropdown
		$subjects = Subject::orderBy('subject_name', 'asc')->get();

		//set subject_id variable, workaround for create function
		$subject_id = $section->subject_id;

		//validate if user can update section (see AuthServiceProvider)
		if ($request->user()->can('update-section', $section)) {
			return view('sections.edit', compact('section','subjects','subject_id'));
		} else {
			abort(403, 'Unauthorized action.');
		}
	}

	public function create(Request $request, Section $section, Subject $subject)
	{
		//retrieve subjects for dropdown
		$subjects = Subject::orderBy('subject_name', 'asc')->get();

		//set subject_id if argument is given
		if ($request->has('subject_id')) {
			$subject_id = $request->input('subject_id');
			$subject = Subject::where('id', $request->input('subject_id'))->first();
		} else {
			$subject_id = null;
			$subject = null;
		}

		//check if the user has the rights permissions
		if (Auth::user()->cant('update-subject', $subject)) {
			abort(403, 'Unauthorized action.');
		}

		return view('sections.create', compact('section','subjects','subject','subject_id'));
	}

	public function store(Request $request)
	{
		//only a superadmin has permissions to create new sections
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'section_name' => 'required|min:4',
			'section_description' => 'required|min:4',
			'subject_id' => 'required'
		]);

		$section = Section::create($request->all());

		Event::fire(new SectionCreated($section));
		return Redirect::route('sections.index', array('subject_id' => $request->input('subject_id')))->with('message', 'Section created');
	}

	public function update(Request $request, Section $section, Subject $subject)
	{
		//validate if user can update section (see AuthServiceProvider)

		if ($request->user()->can('update-section', $section)) {

			//validate input form
			$this->validate($request, [
				'section_name' => 'required|min:4',
				'section_description' => 'required|min:4'
			]);

			$section->update($request->all());

			Event::fire(new SectionUpdated($section));
			return Redirect::route('sections.show', $section->id)->with('message', 'Section updated.');
		} else {
			abort(403, 'Unauthorized action.');
		}
	}

	public function destroy(Section $section, Subject $subject)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//remove all related templates and content
		$templates = Template::where('section_id', $section->id)->get();

		Template::where('section_id', $section->id)->delete();

		//log Event
		Event::fire(new SectionDeleted($section));

		$section->delete();
		return Redirect::route('sections.index')->with('message', 'Section deleted.');
	}

	public function manuals()
	{
		//only non guests can see all sections
		if (Gate::allows('see-nonvisible-content')) {
			$sections = Section::orderBy('section_name', 'asc')->get();
		} else {
			$sections = Section::orderBy('section_name', 'asc')->where('visible', '<>' , 'False')->get();
		}

		//sort sections on natural ordering
		$sections = $sections->sortBy('section_name', SORT_NATURAL);

		//abort if sectionRights array is empty
		if (empty($sections)) {
			abort(403, 'No sections have been found. Please ask your administrator to add any sections.');
		}

		return view('manuals.index', compact('sections'));
	}

	public function showmanual($id)
	{
		$section = Section::where('id', $id)->first();

		//only non guests can see all sections
		if (Gate::allows('see-nonvisible-content')) {
			$templates = Template::with('requirements')->where('section_id', $id)->get();
		} else {
			$templates = Template::with('requirements')->where('visible', '<>' , 'False')->where('section_id', $id)->get();
		}

		if (!$section) {
			abort(403, 'This section no longer exists in the database.');
		}

		//abort if sectionRights array is empty
		if (empty($templates)) {
			abort(403, 'No templates have been found for this selection.');
		}

		//sort templates on natural ordering
		$templates = $templates->sortBy('template_name', SORT_NATURAL);

		return view('manuals.show', compact('section', 'templates'));
	}
}

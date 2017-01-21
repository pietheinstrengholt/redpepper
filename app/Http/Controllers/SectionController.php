<?php

namespace App\Http\Controllers;
use App\AuthService;
use App\FileUpload;
use App\Helpers\ActivityLog;
use App\Http\Controllers\Controller;
use App\Section;
use App\Subject;
use App\Template;
use App\User;
use App\UserRights;
use Auth;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class SectionController extends Controller
{
	protected $authService;

	public function __construct(AuthService $authService)
	{
		 $this->authService = $authService;
	}

	public function show(Subject $subject, Section $section)
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

		return view('sections.show', compact('subject', 'section', 'templates', 'files'));
	}

	public function edit(Subject $subject, Section $section, Request $request)
	{
		//check if id property exists
		if (!$section->id) {
			abort(403, 'This section no longer exists in the database.');
		}

		//check if the user has the rights permissions
		if ($request->user()->cant('update-section', $section)) {
			abort(403, 'Unauthorized action.');
		}

		//retrieve list with subject via the AuthService Model and not by using Auth::user()->subjects;
		$subjectlist = $this->authService->getSubjectsList();
		$subjects = Subject::whereIn('id', $subjectlist)->orderBy('subject_name', 'asc')->get();

		//set subject_id variable, workaround for create function
		$subject_id = $section->subject_id;

		return view('sections.edit', compact('subject','section','subjects','subject_id'));
	}

	public function create(Subject $subject, Section $section, Request $request)
	{
		//check if the user has the rights permissions
		if (Auth::user()->cant('update-subject', $subject)) {
			abort(403, 'Unauthorized action.');
		}

		//retrieve list with subject via the AuthService Model and not by using Auth::user()->subjects;
		$subjectlist = $this->authService->getSubjectsList();
		$subjects = Subject::whereIn('id', $subjectlist)->orderBy('subject_name', 'asc')->get();

		return view('sections.create', compact('section','subjects','subject','subject_id'));
	}

	public function store(Subject $subject, Section $section, Request $request)
	{
		//check if the user has the rights permissions
		if (Auth::user()->cant('update-subject', $subject)) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'section_name' => 'required|min:4',
			'section_description' => 'required|min:4',
			'subject_id' => 'required'
		]);

		$section = Section::create($request->all());

		//Log activity
		ActivityLog::submit("Section " . $section->section_name . " was created.");

		return Redirect::route('subjects.show', array($subject))->with('message', 'Section created');
	}

	public function update(Subject $subject, Section $section, Request $request)
	{
		//validate if user can update section (see AuthServiceProvider)

		if ($request->user()->can('update-section', $section)) {

			//validate input form
			$this->validate($request, [
				'section_name' => 'required|min:4',
				'section_description' => 'required|min:4'
			]);

			$section->update($request->all());

			//Log activity
			ActivityLog::submit("Section " . $section->section_name . " was updated.");

			return Redirect::route('subjects.show', array($subject))->with('message', 'Section updated.');
		} else {
			abort(403, 'Unauthorized action.');
		}
	}

	public function destroy(Subject $subject, Section $section, Request $request)
	{
		//only a superadmin has permissions to create new sections
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//get all related templates and content
		$templates = Template::where('section_id', $section->id)->get();

		//delete underlying templates
		foreach ($templates as $template) {
			Template::where('parent_id', $template->id)->delete();
		}

		//delete underlying templates
		Template::where('section_id', $section->id)->delete();

		//delete underlying files in upload folder
		$files = FileUpload::where('section_id', $section->id)->get();
		foreach ($files as $file) {
			//check if not exists
			if (file_exists(public_path() . '/files/' . $file->file_name)) {
				//remove file from upload folder
				unlink('/' . base_path() . '/public/files/' . $file->file_name);
			}
		}

		//remove files from the database
		FileUpload::where('section_id', $section->id)->delete();

		//Log activity
		ActivityLog::submit("Section " . $section->section_name . " was deleted.");

		$section->delete();
		return Redirect::route('subjects.show', array($subject))->with('message', 'Section deleted.');
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

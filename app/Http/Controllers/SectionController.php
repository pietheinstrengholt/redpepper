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
use Auth;
use Event;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class SectionController extends Controller
{	
    public function index(Request $request)
    {
		//set subject_id if argument is given
		if ($request->has('subject_id')) {
			$subject = Subject::where('id', $request->input('subject_id'))->first();
		} else {
			$subject = null;
		}
		
		//only non guests can see all sections
		if (Auth::guest()) {
			if ($request->has('subject_id')) {
				$sections = Section::with('subject')->orderBy('section_name', 'asc')->where('subject_id', $request->input('subject_id'))->where('visible', '<>' , 'False')->get();
			} else {
				$sections = Section::with('subject')->orderBy('section_name', 'asc')->where('visible', '<>' , 'False')->get();
			}
		} else {
			if ($request->has('subject_id')) {
				$sections = Section::with('subject')->orderBy('section_name', 'asc')->where('subject_id', $request->input('subject_id'))->get();
			} else {
				$sections = Section::with('subject')->orderBy('section_name', 'asc')->get();
			}
		}
		
		//sort sections on natural ordering
		$sections = $sections->sortBy('section_name', SORT_NATURAL);
		
		//abort if sectionRights array is empty
		if (empty($sections)) {
			abort(403, 'No sections have been found. Please ask your administrator to add any sections.');
		}
		
		return view('sections.index', compact('sections','subject'));
    }

    public function manuals()
    {
		//only non guests can see all sections
		if (Auth::guest()) {
			$sections = Section::orderBy('section_name', 'asc')->where('visible', '<>' , 'False')->get();
		} else {
			$sections = Section::orderBy('section_name', 'asc')->get();
		}

		//sort sections on natural ordering
		$sections = $sections->sortBy('section_name', SORT_NATURAL);

		//abort if sectionRights array is empty
		if (empty($sections)) {
			abort(403, 'No sections have been found. Please ask your administrator to add any sections.');
		}

		return view('manuals.index', compact('sections'));
    }

    public function show(Section $section)
    {
		//check if id property exists
		if (!$section->id) {
			abort(403, 'This section no longer exists in the database.');
		}

		//check if visible is set to false and user is a guest
		if (Auth::guest() && $section->visible == "False") {
			abort(403, 'Unauthorized action.');
		}

		//only non guests will see the hidden templates
		if (Auth::guest()) {
			$templates = Template::orderBy('template_name', 'asc')->where('section_id', $section->id)->where('parent_id', null)->orWhere('parent_id', 0)->where('visible', '<>' , 'False')->get();
		} else {
			$templates = Template::orderBy('template_name', 'asc')->where('section_id', $section->id)->where('parent_id', null)->orWhere('parent_id', 0)->get();
		}

		//sort templates on natural ordering
		$templates = $templates->sortBy('template_name', SORT_NATURAL);

		return view('sections.show', compact('section', 'templates'));
    }

    public function showmanual($id)
    {
		$section = Section::where('id', $id)->first();
		
		//only non guests can see all sections
		if (Auth::guest()) {
			$templates = Template::with('requirements')->where('visible', '<>' , 'False')->where('section_id', $id)->get();
		} else {
			$templates = Template::with('requirements')->where('section_id', $id)->get();
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

	public function edit(Request $request, Section $section)
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

	public function create(Request $request, Section $section)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//retrieve subjects for dropdown
		$subjects = Subject::orderBy('subject_name', 'asc')->get();
		
		//set subject_id if argument is given
		if ($request->has('subject_id')) {
			$subject_id = $request->input('subject_id');
		} else {
			$subject_id = null;
		}

		return view('sections.create', compact('section','subjects','subject_id'));
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
		return Redirect::route('sections.index')->with('message', 'Section created');
	}

	public function update(Request $request, Section $section)
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

	public function destroy(Section $section)
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
}

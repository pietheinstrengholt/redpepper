<?php

namespace App\Http\Controllers;
use DB;
use App\Section;
use App\Template;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Input;
use Redirect;

use Gate;
use App\User;
use Auth;

class SectionController extends Controller
{
    public function index(Request $request)
    {
		//only superadmin can see all sections
		if (Gate::denies('superadmin')) {
			if ($request->input('group') == "corep") {
				$sections = Section::orderBy('section_name', 'asc')->where('subject_id', 1)->where('visible', 'True')->get();
			} elseif ($request->input('group') == "finrep") {
				$sections = Section::orderBy('section_name', 'asc')->where('subject_id', 2)->where('visible', 'True')->get();
			} elseif ($request->input('group') == "liquidity") {
				$sections = Section::orderBy('section_name', 'asc')->where('subject_id', 3)->where('visible', 'True')->get();
			} elseif ($request->input('group') == "other") {
				$sections = Section::orderBy('section_name', 'asc')->where('subject_id', 4)->where('visible', 'True')->get();
			} else {
				$sections = Section::orderBy('section_name', 'asc')->where('visible', 'True')->get();
			}
		} else {
			if ($request->input('group') == "corep") {
				$sections = Section::orderBy('section_name', 'asc')->where('subject_id', 1)->get();
			} elseif ($request->input('group') == "finrep") {
				$sections = Section::orderBy('section_name', 'asc')->where('subject_id', 2)->get();
			} elseif ($request->input('group') == "liquidity") {
				$sections = Section::orderBy('section_name', 'asc')->where('subject_id', 3)->get();
			} elseif ($request->input('group') == "other") {
				$sections = Section::orderBy('section_name', 'asc')->where('subject_id', 4)->get();
			} else {
				$sections = Section::orderBy('section_name', 'asc')->get();
			}		
		}
		return view('sections.index', compact('sections'));
    }
	
    public function manuals()
    {
		//only superadmin can see all sections
		if (Gate::denies('superadmin')) {
			$sections = Section::orderBy('section_name', 'asc')->where('visible', 'True')->get();
		} else {
			$sections = Section::orderBy('section_name', 'asc')->get();	
		}
		return view('manuals.index', compact('sections'));
    }	
	
    public function show(Section $section)
    {
		//check if visible is set to false and user is a guest
		if (Auth::guest() && $section->visible == "False") {
			abort(403, 'Unauthorized action.');
		}
	
		//only superadmin can see all templates
		if (Gate::denies('superadmin')) {
			$templates = Template::orderBy('template_name', 'asc')->where('section_id', $section->id)->where('visible', 'True')->get();
		} else {
			$templates = Template::orderBy('template_name', 'asc')->where('section_id', $section->id)->get();		
		}
		return view('sections.show', compact('section', 'templates'));
    }
	
    public function showmanual($id)
    {
		$section = Section::where('id', $id)->first();
		$templates = Template::with('requirements')->where('section_id', $id)->get();
		return view('manuals.show', compact('section', 'templates'));
    }	

	public function edit(Section $section)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
	
		return view('sections.edit', compact('section'));
	}	
	
	public function create(Section $section)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
	
		return view('sections.create', compact('section'));
	}
	
	public function store(Request $request)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
		
		//validate input form
		$this->validate($request, [
			'section_name' => 'required',
			'section_description' => 'required',
			'subject_id' => 'required'
		]);
	
		$input = Input::all();
		Section::create($input);
		return Redirect::route('sections.index')->with('message', 'Section created');
	}
	 
	public function update(Section $section, Request $request)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
		
		//validate input form
		$this->validate($request, [
			'section_name' => 'required',
			'section_description' => 'required',
			'subject_id' => 'required'
		]);		

		$input = array_except(Input::all(), '_method');
		$section->update($input);
		return Redirect::route('sections.show', $section->id)->with('message', 'Section updated.');
	}
	 
	public function destroy(Section $section)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

		//remove all related templates and content
		$templates = Template::where('section_id', $section->id)->get();
		
		if (!empty($templates)) {
			foreach ($templates as $template) {
				TemplateRow::where('template_id', $section->id)->delete();
				TemplateColumn::where('template_id', $section->id)->delete();
				TemplateField::where('template_id', $section->id)->delete();
				Requirement::where('template_id', $section->id)->delete();
				Technical::where('template_id', $section->id)->delete();
				ChangeRequest::where('template_id', $section->id)->delete();				
			}
		}
		
		Template::where('section_id', $section->id)->delete();
		
		$section->delete();
		return Redirect::route('sections.index')->with('message', 'Section deleted.');
	}
}

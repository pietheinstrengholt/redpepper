<?php

namespace App\Http\Controllers;
use DB;
use App\Section;
use App\Template;
use App\TemplateRow;
use App\TemplateColumn;
use App\TemplateField;
use App\Requirement;

use App\Technical;
use App\TechnicalType;
use App\TechnicalSource;

use App\ChangeRequest;
use App\DraftField;
use App\DraftRequirement;
use App\DraftTechnical;

use App\HistoryRequirement;
use App\HistoryTechnical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Input;
use Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Session;

class SearchController extends Controller
{

	public function search(Request $request)
	{
		
		//validate input form
		$this->validate($request, [
			'search' => 'required'
		]);	
		
		if ($request->isMethod('post')) {

			if ($request->has('search')) {

				if ($request->has('advanced-search')) {
					
					//validate input form
					$this->validate($request, [
						'sections' => 'required',
						'types' => 'required'
					]);

					//set typeslist
					$typeslist = $request->input('types');

					//create empty array to fill with template_id's
					$templatesArray = array();

					//retrieve template_id's for each section selected
					foreach($request->input('sections') as $section) {
						$templates = Template::where('section_id', $section)->get();
						if (!empty($templates)) {
							foreach($templates as $template) {
								array_push($templatesArray, $template['id']);
							}
						}
					}

					return view('search.index', [
						'rows' => TemplateRow::where('row_description', 'like', '%' . $request->input('search') . '%')->whereIn('template_id', $templatesArray)->get(),
						'columns' => TemplateColumn::where('column_description', 'like', '%' . $request->input('search') . '%')->whereIn('template_id', $templatesArray)->get(),
						'requirements' => Requirement::where('content', 'like', '%' . $request->input('search') . '%')->whereIn('template_id', $templatesArray)->whereIn('content_type', $typeslist)->get(),
						'technicals' => Technical::where('content', 'like', '%' . $request->input('search') . '%')->orWhere('description', 'like', '%' . $request->input('search') . '%')->whereIn('template_id', $templatesArray)->get(),
						'fields' => TemplateField::where('content', 'like', '%' . $request->input('search') . '%')->where('property', '<>', 'disabled')->whereIn('template_id', $templatesArray)->whereIn('property', $typeslist)->get()
					]);

				} else {
					//no types and selections are selected
					return view('search.index', [
						'rows' => TemplateRow::where('row_description', 'like', '%' . $request->input('search') . '%')->get(),
						'columns' => TemplateColumn::where('column_description', 'like', '%' . $request->input('search') . '%')->get(),
						'requirements' => Requirement::where('content', 'like', '%' . $request->input('search') . '%')->get(),
						'technicals' => Technical::where('content', 'like', '%' . $request->input('search') . '%')->orWhere('description', 'like', '%' . $request->input('search') . '%')->get(),
						'fields' => TemplateField::where('content', 'like', '%' . $request->input('search') . '%')->where('property', '<>', 'disabled')->get()
					]);
				}
			} else {
				abort(403, 'No search argument entered.');
			}
		}
	}

	public function advancedsearch()
	{
		return view('search.advanced', [
			'sections' => Section::where('visible', 'True')->orderBy('section_name', 'asc')->get()
		]);
	}

}

<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Requirement;
use App\Section;
use App\Technical;
use App\TechnicalSource;
use App\TechnicalType;
use App\Template;
use App\TemplateColumn;
use App\TemplateRow;
use Illuminate\Http\Request;
use Redirect;
use Session;
use Validator;

class SearchController extends Controller
{

	public function search(Request $request)
	{
		//validate input form
		$this->validate($request, [
			'search' => 'required'
		]);

		if ($request->isMethod('post') || $request->isMethod('get')) {
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
						'content' => Requirement::where('content', 'like', '%' . $request->input('search') . '%')->where('content_type', '<>', 'disabled')->whereIn('template_id', $templatesArray)->whereIn('content_type', $typeslist)->get(),
						'technicals' => Technical::where('content', 'like', '%' . $request->input('search') . '%')->orWhere('description', 'like', '%' . $request->input('search') . '%')->whereIn('template_id', $templatesArray)->get(),
						'search' => $request->input('search')
					]);

				} else {
					//no types and selections are selected
					return view('search.index', [
						'rows' => TemplateRow::where('row_description', 'like', '%' . $request->input('search') . '%')->get(),
						'columns' => TemplateColumn::where('column_description', 'like', '%' . $request->input('search') . '%')->get(),
						'content' => Requirement::where('content', 'like', '%' . $request->input('search') . '%')->where('content_type', '<>', 'disabled')->get(),
						'technicals' => Technical::where('content', 'like', '%' . $request->input('search') . '%')->orWhere('description', 'like', '%' . $request->input('search') . '%')->get(),
						'search' => $request->input('search')
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

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
use App\User;
use Auth;
use Event;
use Gate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Redirect;
use Session;
use Validator;

class CSVController extends Controller
{
	public function uploadcsv(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
        }

		//validate input form
		$this->validate($request, [
			'csv' => 'required',
			'formname' => 'required'
		]);

		if ($request->isMethod('post')) {

			if ($request->hasFile('csv')) {
				if ($request->file('csv')->isValid()) {

					$file = array('csv' => $request->file('csv'));
					
					if ($request->file('csv')->getClientOriginalExtension() <> 'csv') {
						abort(403, 'Unable to import CSV file, filetype is no CSV');
					}

					Excel::load($request->file('csv'), function ($reader) use ($request) {

						//create array from csv content
						$csvarray = $reader->toArray();
						
						if (empty($csvarray)) {
							abort(403, 'Unable to import CSV file, no content is found');
						}
						

						//import rows function
						if ($request->input('formname') == "importtech" && $request->has('section_id')) {

							$section = Section::findOrFail($request->input('section_id'));
							$templates = Template::where('section_id', $request->input('section_id'))->get();

							//start counting
							$i = 0;

							foreach ($csvarray as $csv) {

								if (array_key_exists('template_id', $csv) && array_key_exists('row_code', $csv) && array_key_exists('column_code', $csv) && array_key_exists('source', $csv) && array_key_exists('type', $csv)  && array_key_exists('value', $csv)  && array_key_exists('description', $csv)) {
									
									//remove existing content if count is zero
									if ($i = 0) {
										if (!empty($templates)) {
											foreach ($templates as $template) {
												Technical::where('template_id', $template->id)->delete();
											}
										}
									}
									
									$technical = new Technical;
									$technical->template_id = $csv['template_id'];
									$technical->row_code = $csv['row_code'];
									$technical->column_code = $csv['column_code'];
									$technical->source_id = $csv['source'];
									$technical->type_id = $csv['type'];
									$technical->content = $csv['value'];
									$technical->description = $csv['description'];
									$technical->created_by = Auth::user()->id;
									$technical->save();
								} else {
									abort(403, 'Unable to import CSV file, header is incorrect');
								}
								
								//increate increment
								$i++;
							}
						}

						//import rows function
						if ($request->input('formname') == "importrows" && $request->has('template_id')) {

							$template = Template::findOrFail($request->input('template_id'));

							foreach ($csvarray as $csv) {

								if (array_key_exists('template_id', $csv) && array_key_exists('row_num', $csv) && array_key_exists('row_code', $csv) && array_key_exists('row_description', $csv)) {
									
									//remove existing content if count is zero
									if ($i = 0) {
										if (!empty($templates)) {
											foreach ($templates as $template) {
												TemplateRow::where('template_id', $request->input('template_id'))->delete();
											}
										}
									}
									
									$row = new TemplateRow;
									$row->template_id = $request->input('template_id');
									$row->row_num = $csv['row_num'];
									$row->row_code = $csv['row_code'];
									$row->row_description = $csv['row_description'];
									//$row->row_reference = $rowline['row_reference'];
									$row->created_by = Auth::user()->id;
									$row->save();
								} else {
									abort(403, 'Unable to import CSV file, header is incorrect');
								}
								
								//increate increment
								$i++;
							}
						}

						//import columns function
						if ($request->input('formname') == "importcolumns" && $request->has('template_id')) {

							$template = Template::findOrFail($request->input('template_id'));

							foreach ($csvarray as $csv) {

								if (array_key_exists('template_id', $csv) && array_key_exists('column_num', $csv) && array_key_exists('column_code', $csv) && array_key_exists('column_description', $csv)) {
									
									//remove existing content if count is zero
									if ($i = 0) {
										if (!empty($templates)) {
											foreach ($templates as $template) {
												TemplateColumn::where('template_id', $request->input('template_id'))->delete();
											}
										}
									}
									
									$column = new TemplateColumn;
									$column->template_id = $request->input('template_id');
									$column->column_num = $csv['column_num'];
									$column->column_code = $csv['column_code'];
									$column->column_description = $csv['column_description'];
									$column->created_by = Auth::user()->id;
									$column->save();
								} else {
									abort(403, 'Unable to import CSV file, header is incorrect');
								}

								//increate increment
								$i++;
							}
						}

						//import fields function
						if ($request->input('formname') == "importcontent" && $request->has('template_id')) {

							$template = Template::findOrFail($request->input('template_id'));

							foreach ($csvarray as $csv) {

								if (array_key_exists('template_id', $csv) && array_key_exists('content_type', $csv) && array_key_exists('content', $csv) && (array_key_exists('row_code', $csv) || array_key_exists('column_code', $csv))) {
									
									//remove existing content if count is zero
									if ($i = 0) {
										if (!empty($templates)) {
											foreach ($templates as $template) {
												Requirement::where('template_id', $request->input('template_id'))->delete();
											}
										}
									}
									
									$Requirement = new Requirement;
									$Requirement->template_id = $request->input('template_id');
									$Requirement->row_code = $csv['row_code'];
									$Requirement->column_code = $csv['column_code'];
									$Requirement->content_type = $csv['content_type'];
									$Requirement->content = $csv['content'];
									$Requirement->created_by = Auth::user()->id;
									$Requirement->save();
								} else {
									abort(403, 'Unable to import CSV file, header is incorrect');
								}
								
								//increate increment
								$i++;
							}
						}

					});

				}

			}

			return Redirect::to('/sections')->with('message', 'CSV Imported successfully to the database.');
		}
	}


	public function importtech()
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$sections = Section::all();
		return view('csv.importtech', compact('sections'));
	}

	public function importrows()
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$templates = Template::all();
		return view('csv.importrows', compact('templates'));
	}

	public function importcolumns()
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$templates = Template::all();
		return view('csv.importcolumns', compact('templates'));
	}

	public function importcontent()
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		$templates = Template::all();
		return view('csv.importcontent', compact('templates'));
	}

}

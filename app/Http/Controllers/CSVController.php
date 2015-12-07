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

use Gate;
use App\User;
use Auth;

class CSVController extends Controller
{
	public function uploadcsv(Request $request) 
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }		
	
		if ($request->isMethod('post')) {
		
			if ($request->hasFile('csv')) {
				if ($request->file('csv')->isValid()) {
					$file = array('csv' => Input::file('csv'));

					Excel::load(Input::file('csv'), function ($reader) use ($request) {
	
						//create array from csv content
						$csvarray = $reader->toArray();

						//import rows function
						if ($request->input('formname') == "importrows" && $request->has('section_id')) {
							
							$templates = Template::where('section_id', $request->has('section_id'))->get();
							
							if (!empty($templates)) {
								foreach ($templates as $template) {
									Template::where('section_id', $template->id)->delete();
								}
							}

							foreach ($csvarray as $csv) {
								
								if (array_key_exists('template_id', $csv) && array_key_exists('row_number', $csv) && array_key_exists('column_number', $csv) && array_key_exists('source', $csv) && array_key_exists('type', $csv)  && array_key_exists('value', $csv)  && array_key_exists('description', $csv)) {							
									$technical = new Technical;
									$technical->template_id = $csv['template_id'];
									$technical->row_num = $csv['row_number'];
									$technical->col_num = $csv['column_number'];									
									$technical->source_id = $csv['source'];
									$technical->type_id = $csv['type'];
									$technical->content = $csv['value'];
									$technical->description = $csv['description'];
									$technical->created_by = Auth::user()->id;
									$technical->save();
								} else {
									echo "Error: header mismatch!";
									exit();
								}
							}
						}
						
						//import rows function
						if ($request->input('formname') == "importrows" && $request->has('template_id')) {
							
							TemplateRow::where('template_id', $request->has('template_id'))->delete();

							foreach ($csvarray as $csv) {
								
								if (array_key_exists('template_id', $csv) && array_key_exists('row_num', $csv) && array_key_exists('row_name', $csv) && array_key_exists('row_description', $csv)) {							
									$row = new TemplateRow;
									$row->template_id = $csv['template_id'];
									$row->row_num = $csv['row_num'];
									$row->row_name = $csv['row_name'];
									$row->row_description = $csv['row_description'];
									//$row->row_reference = $rowline['row_reference'];
									$row->created_by = Auth::user()->id;
									$row->save();
								} else {
									echo "Error: header mismatch!";
									exit();
								}
							}
						}
						
						//import columns function
						if ($request->input('formname') == "importcolumns" && $request->has('template_id')) {
							
							TemplateColumn::where('template_id', $request->has('template_id'))->delete();
						
							foreach ($csvarray as $csv) {
								
								if (array_key_exists('template_id', $csv) && array_key_exists('column_num', $csv) && array_key_exists('column_name', $csv) && array_key_exists('column_description', $csv)) {							
									$column = new TemplateColumn;
									$column->template_id = $csv['template_id'];
									$column->column_num = $csv['column_num'];
									$column->column_name = $csv['column_name'];
									$column->column_description = $csv['column_description'];
									$column->created_by = Auth::user()->id;
									$column->save();
								} else {
									echo "Error: header mismatch!";
									exit();
								}
							}
						}
						
						//import fields function
						if ($request->input('formname') == "importfields" && $request->has('template_id')) {
							
							TemplateField::where('template_id', $request->has('template_id'))->delete();
						
							foreach ($csvarray as $csv) {
								
								if (array_key_exists('template_id', $csv) && array_key_exists('row_number', $csv) && array_key_exists('column_number', $csv) && array_key_exists('property', $csv) && array_key_exists('content', $csv)) {							
									$TemplateField = new TemplateField;
									$TemplateField->template_id = $csv['template_id'];
									$TemplateField->row_name = $csv['row_number'];
									$TemplateField->column_name = $csv['column_number'];
									$TemplateField->property = $csv['property'];
									$TemplateField->content = $csv['content'];
									$TemplateField->created_by = Auth::user()->id;
									$TemplateField->save();
								} else {
									echo "Error: header mismatch!";
									exit();
								}
							}
						}
						
						//import content function
						if ($request->input('formname') == "importcontent" && $request->has('template_id')) {
							
							Requirement::where('template_id', $request->has('template_id'))->delete();
						
							foreach ($csvarray as $csv) {
								
								if (array_key_exists('template_id', $csv) && array_key_exists('field_id', $csv) && array_key_exists('content_type', $csv) && array_key_exists('content', $csv)) {							
									$Requirements = new Requirement;
									$Requirements->template_id = $csv['template_id'];
									$Requirements->field_id = $csv['field_id'];
									$Requirements->content_type = $csv['content_type'];
									$Requirements->content = $csv['content'];
									$Requirements->created_by = Auth::user()->id;
									$Requirements->save();
								} else {
									echo "Error: header mismatch!";
									exit();
								}
							}
						}						
						
					});

				}
			
			}

			return Redirect::to('/');
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
	
	public function importfields() 
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }		
	
		$templates = Template::all();
		return view('csv.importfields', compact('templates'));
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
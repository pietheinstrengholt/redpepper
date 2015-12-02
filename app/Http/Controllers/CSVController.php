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

class CSVController extends Controller
{

	public function uploadcsv(Request $request) 
	{
		if ($request->isMethod('post')) {
		
			if ($request->hasFile('csv')) {
				if ($request->file('csv')->isValid()) {
					$file = array('csv' => Input::file('csv'));
					
					echo "<h2>Excel import section_id: " . $request->input('section_id') . "</h2>";
					
					echo "<h2>Excel import template_id: " . $request->input('template_id') . "</h2>";					
					
					if ($request->has('template_name')) {
						echo "<h2>Excel import template: " . $request->input('template_name') . "</h2>";
					} else {
						echo "Error: no template name entered!";
						//exit();
					}
					
					if ($request->has('formname')) {
						echo "<h3>Formname description: " . $request->input('formname') . "</h3>";
					}
								
					Excel::load(Input::file('csv'), function ($reader) use ($request) {
	
						$csvarray = $reader->toArray();
						
						echo "<pre>";
						print_r($csvarray);
						echo "</pre>";
						
						foreach ($csvarray as $csv) {
							
							if (array_key_exists('template_id', $csv) && array_key_exists('row_num', $csv) && array_key_exists('row_name', $csv) && array_key_exists('row_description', $csv)) {
								echo "<pre>";
								print_r($csv);
								echo "</pre>";
							}
							

						}
						
						
					});

				}
			
			}

			//return Redirect::to('/');
		}
	}	


	public function importtech() 
	{
		$sections = Section::all();
		return view('csv.importtech', compact('sections'));
	}
	
	public function importrows() 
	{
		$templates = Template::all();
		return view('csv.importrows', compact('templates'));
	}
	
	public function importcolumns() 
	{
		$templates = Template::all();
		return view('csv.columns', compact('templates'));
	}	

}

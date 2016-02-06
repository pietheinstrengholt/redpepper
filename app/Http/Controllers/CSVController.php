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
use App\Events\SectionUpdated;
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
					
					//Abort if the file extension is not CSV
					if ($request->file('csv')->getClientOriginalExtension() <> 'csv') {
						abort(403, 'Unable to import CSV file, filetype is no CSV');
					}

					//Use Excel functionality to parse CSV file
					Excel::load($request->file('csv'), function ($reader) use ($request) {

						//Create array from csv content
						$csvarray = $reader->toArray();
						
						//Abort when only a header is used and no content can be found
						if (empty($csvarray)) {
							abort(403, 'Unable to import CSV file, no content is found');
						}

						//import rows function
						if ($request->input('formname') == "importtech" && $request->has('section_id')) {

							$section = Section::findOrFail($request->input('section_id'));
							$templates = Template::where('section_id', $request->input('section_id'))->get();
							$sources =  TechnicalSource::all();
							$types =  TechnicalType::all();
							
							//Abort when there are no templates
							if (empty($templates)) {
								abort(403, 'Unable to import CSV file, no templates found for this section');
							}
							
							//Abort when there are no sources
							if (empty($sources)) {
								abort(403, 'Unable to import CSV file, no sources are found in the database');
							}
							
							//Abort when there are no types
							if (empty($types)) {
								abort(403, 'Unable to import CSV file, no types are found in the database');
							}

							//Create empty array for template id validation
							$templatesArray = array();
							foreach ($templates as $template) {
								array_push($templatesArray,$template->id);
							}
							
							//Create empty array for template id validation
							$sourcesArray = array();
							foreach ($sources as $source) {
								array_push($sourcesArray,$source->id);
							}
							
							//Create empty array for template id validation
							$typesArray = array();
							foreach ($types as $type) {
								array_push($typesArray,$type->id);
							}
						
							//validate before import CSV content
							//TODO: validate in CSV for existing row_code, column_code in template
							foreach ($csvarray as $csv) {
								if (!(array_key_exists('template_id', $csv) && array_key_exists('row_code', $csv) && array_key_exists('column_code', $csv) && array_key_exists('source_id', $csv) && array_key_exists('type_id', $csv)  && array_key_exists('content', $csv)  && array_key_exists('description', $csv))) {
									abort(403, 'Unable to import CSV file, header is incorrect');
								}
								if (!in_array($csv['template_id'], $templatesArray)) {
									abort(403, 'Template id used in CSV file does\'t belong to selected section');
								}
								if (!in_array($csv['source_id'], $sourcesArray)) {
									abort(403, 'source id used in CSV file can\'t be matched with any source id in the database');
								}
								if (!in_array($csv['type_id'], $typesArray)) {
									abort(403, 'type id used in CSV file can\'t be matched with any type id in the database');
								}
							}
							
							//remove existing content if count is zero
							foreach ($templates as $template) {
								Technical::where('template_id', $template->id)->delete();
							}

							//Import CSV content
							foreach ($csvarray as $csv) {
								$technical = new Technical;
								$technical->template_id = $csv['template_id'];
								$technical->row_code = $csv['row_code'];
								$technical->column_code = $csv['column_code'];
								$technical->source_id = $csv['source_id'];
								$technical->type_id = $csv['type_id'];
								$technical->content = $csv['content'];
								$technical->description = $csv['description'];
								$technical->created_by = Auth::user()->id;
								$technical->save();
							}
							
							//log Event
							Event::fire(new SectionUpdated($section));
						}
					});
				}
			}

			return Redirect::to('/sections')->with('message', 'CSV successfully imported to the database.');
		}
	}

	public function import()
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$sections = Section::all();
		return view('csv.import', compact('sections'));
	}
}
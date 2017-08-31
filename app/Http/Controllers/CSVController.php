<?php

namespace App\Http\Controllers;
use App\Helpers\ActivityLog;
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
use Gate;
use Illuminate\Http\Request;
use Input;
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

		//show error when file is to large
		if ( !empty($_SERVER['CONTENT_LENGTH']) && empty($_FILES) && empty($_POST) ) {
			abort(403, 'The uploaded file was too large. You must upload a file smaller than ' . ini_get("upload_max_filesize"));
		}

		//validate input form
		$this->validate($request, [
			'section_id' => 'required',
			'csv' => 'required'
		]);

		//validate if message type is post
		if ($request->isMethod('post')) {

			//check if file is valid
			if (Input::file('csv')->isValid()) {

				//get file content
				$file = Input::file('csv');

				$content = file_get_contents($file->getPathName());

				//Abort if the file extension is not CSV
				if (Input::file('csv')->getClientOriginalExtension() <> 'csv') {
					abort(403, 'Unable to import CSV file, filetype is no CSV');
				}

				//parse CSV string
				$lines = explode(PHP_EOL, $content);
				$array = array();
				foreach ($lines as $line) {
				    $array[] = str_getcsv($line);
				}

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

				foreach ($array as $key => $record) {
					//explode text lines
					$exploded = explode(';', $record[0]);

					//first line is the header, validate csv structure, abort when incorrect items are found
					if ($key == 0) {
						if ($exploded[0] != "template_id") {
							abort(403, 'Header does not contain template_id');
						}
						if ($exploded[1] != "row_code") {
							abort(403, 'Header does not contain row_code');
						}
						if ($exploded[2] != "column_code") {
							abort(403, 'Header does not contain column_code');
						}
						if ($exploded[3] != "source_id") {
							abort(403, 'Header does not contain source_id');
						}
						if ($exploded[4] != "type_id") {
							abort(403, 'Header does not contain type_id');
						}
						if ($exploded[5] != "content") {
							abort(403, 'Header does not contain content');
						}
						if ($exploded[6] != "description") {
							abort(403, 'Header does not contain description');
						}
					}

					//validate content. 6 values used is the minimum. The description might be empty.
					//TODO: validate in CSV for existing row_code, column_code in template
					if ($key > 0 && count($exploded) > 5) {
						if (!in_array($exploded[0], $templatesArray)) {
							abort(403, 'There have been incorrect template_id found that does\'t belong to selected section');
						}
						if (empty($exploded[1])) {
							abort(403, 'There have an empty row_code used.');
						}
						if (empty($exploded[2])) {
							abort(403, 'There have an empty column_code used.');
						}
						if (!in_array($exploded[3], $sourcesArray)) {
							abort(403, 'There have been incorrect source_id\'s found that can\'t be matched with any source_id in the database');
						}
						if (!in_array($exploded[4], $typesArray)) {
							abort(403, 'There have been incorrect type_id\'s found that can\'t be matched with any type_id in the database');
						}
					}
				}

				//all validations have been passed, it's time to delete all existing content
				foreach ($templates as $template) {
					Technical::where('template_id', $template->id)->delete();
				}

				//add new content to database
				foreach ($array as $key => $record) {
					//explode text lines
					$exploded = explode(';', $record[0]);

					if ($key > 0 && count($exploded) > 5) {
						$technical = new Technical;
						$technical->template_id = $exploded[0];
						$technical->row_code = $exploded[1];
						$technical->column_code = $exploded[2];
						$technical->source_id = $exploded[3];
						$technical->type_id = $exploded[4];
						$technical->content = $exploded[5];
						if (!empty($exploded[6])) {
							$technical->description = $exploded[6];
						}
						$technical->created_by = Auth::user()->id;
						$technical->save();
					}
				}

				//log activity
				ActivityLog::submit("CSV content imported.");

				return Redirect::to('/subjects')->with('message', 'CSV successfully imported to the database.');
			}
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

	public function seeids()
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$templates = Template::all();
		$sources =  TechnicalSource::all();
		$types =  TechnicalType::all();
		return view('csv.seeids', compact('templates','sources','types'));
	}
}

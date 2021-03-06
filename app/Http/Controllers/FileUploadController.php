<?php
namespace App\Http\Controllers;
use App\FileUpload;
use App\Helpers\ActivityLog;
use App\Http\Controllers\Controller;
use App\Section;
use App\User;
use Auth;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class FileUploadController extends Controller
{
	public function index()
	{
		$files = FileUpload::orderBy('file_name', 'asc')->whereNull('section_id')->get();
		return view('fileupload.index', compact('files'));
	}

	public function edit(FileUpload $fileupload)
	{
		//if section_id is set, check if user has correct rights
		if ($fileupload->section_id) {
			$section = Section::where('id', $fileupload->section_id)->first();
			if (Auth::user()->cant('update-section', $section)) {
				abort(403, 'Unauthorized action.');
			}
		} else {
			//check for superadmin permissions
			if (Gate::denies('superadmin')) {
				abort(403, 'Unauthorized action.');
			}
		}

		//check if id property exists
		if (!$fileupload->id) {
			abort(403, 'This file no longer exists in the database.');
		}

		return view('fileupload.edit', compact('fileupload'));
	}

	public function create(FileUpload $fileupload, Request $request)
	{
		//if section_id is provided use this in the view
		if ($request->has('section_id')) {
			$section = Section::where('id', $request->input('section_id'))->first();
			if ($request->user()->can('update-section', $section)) {
				$section_id = $request->input('section_id');
				return view('fileupload.create', compact('fileupload','section_id'));
			}
		} else {
			//check for superadmin permissions
			if (Gate::denies('superadmin')) {
				abort(403, 'Unauthorized action.');
			}
			return view('fileupload.create', compact('fileupload'));
		}
	}

	public function store(Request $request)
	{
		//validate input form
		$this->validate($request, [
			'fileupload' => 'required',
			'file_description' => 'required'
		]);

		//show error when file is to large
		if ( !empty($_SERVER['CONTENT_LENGTH']) && empty($_FILES) && empty($_POST) ) {
			abort(403, 'The uploaded file was too large. You must upload a file smaller than ' . ini_get("upload_max_filesize"));
		}

		if ($request->file('fileupload')) {

			//set path based on section_id argument
			if ($request->has('section_id')) {
				$section = Section::findOrFail($request->input('section_id'));
				if ($request->user()->can('update-section', $section)) {
					$path = '/files/' . $request->input('section_id') . '/';
				}
			} else {
				//check for superadmin permissions
				if (Gate::denies('superadmin')) {
					abort(403, 'Unauthorized action.');
				}
				$path = '/files/';
			}

			//create files upload folder, if not exists
			if (!file_exists(public_path() . $path)) {
				mkdir(public_path() . $path, 0777, true);
			}

			//upload image with random string
			$file = $request->file('fileupload');
			$extension = $file->getClientOriginalExtension();
			$filename = $file->getClientOriginalName();

			$validExtensions = array("pdf", "doc", "docx", "xls", "xlsx");

			//validate if file has the right extension
			if (in_array(strtolower($extension), $validExtensions)) {

				$filecheck = FileUpload::where('file_name', $filename)->get();
				$filecheck = $filecheck->toArray();

				//exit if file name is not unique
				if ($filecheck) {
					abort(403, 'An error occurred while processing the file. The file already exists.');
				}
			} else {
				abort(403, 'An error occurred while processing the file. Unknown extension type.');
			}
		}

		//Save file
		$fileupload = new FileUpload;
		if ($request->has('section_id')) {
			$fileupload->section_id = $request->input('section_id');
		}
		$fileupload->file_name = $filename;
		$fileupload->file_description = $request->input('file_description');
		$fileupload->created_by = Auth::user()->id;
		$fileupload->save();

		//Move file to files folder
		$file->move(public_path() . $path, $filename);

		//Log activity
		ActivityLog::submit("File has been uploaded.");

		//redirect based whether a section_id has been provided
		if ($request->has('section_id')) {
			return Redirect::route('subjects.sections.show', array($section->subject, $section))->with('message', 'File uploaded.');
		} else {
			return Redirect::route('fileupload.index')->with('message', 'File uploaded.');
		}
	}

	public function update(FileUpload $fileupload, Request $request)
	{
		//validate input form
		$this->validate($request, [
			'file_description' => 'required'
		]);

		$fileupload->update($request->all());

		//Log activity
		ActivityLog::submit("File has been updated.");

		//redirect based whether a section_id has been provided
		if ($request->has('section_id')) {
			$section = Section::where('id', $request->input('section_id'))->first();
			if ($request->user()->can('update-section', $section)) {
				return Redirect::route('subjects.sections.show', array($section->subject, $section))->with('message', 'File details updated.');
			}
		} else {
			//check for superadmin permissions
			if (Gate::denies('superadmin')) {
				abort(403, 'Unauthorized action.');
			}
			return Redirect::route('fileupload.show', $fileupload->slug)->with('message', 'File details updated.');
		}
	}

	public function destroy(FileUpload $fileupload)
	{
		//check if not exists
		if (file_exists(public_path() . '/files/' . $fileupload->file_name)) {
			//remove file from upload folder
			unlink('/' . base_path() . '/public/files/' . $fileupload->file_name);
		}

		//Log activity
		ActivityLog::submit("File has been deleted.");

		//redirect based whether a section_id has been provided
		if ($fileupload->section_id) {
			$section = Section::where('id', $fileupload->section_id)->first();
			//check if user has rights to delete file
			if (Auth::user()->cant('update-section', $section)) {
				abort(403, 'Unauthorized action.');
			}
			$fileupload->delete();
			return Redirect::route('subjects.sections.show', array($section->subject, $section))->with('message', 'File deleted.');
		} else {
			//check if user has rights to delete file
			if (Gate::denies('superadmin')) {
				abort(403, 'Unauthorized action.');
			}
			$fileupload->delete();
			return Redirect::route('fileupload.index')->with('message', 'File deleted.');
		}
	}
}

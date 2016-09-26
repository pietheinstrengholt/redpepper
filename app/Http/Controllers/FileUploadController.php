<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\FileUpload;
use App\User;
use Gate;
use Auth;
use Illuminate\Http\Request;
use Redirect;
class FileUploadController extends Controller
{
	public function index()
	{
		$files = FileUpload::orderBy('file_name', 'asc')->get();
		return view('fileupload.index', compact('files'));
	}

	public function edit(FileUpload $fileupload)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		//check if id property exists
		if (!$fileupload->id) {
			abort(403, 'This file no longer exists in the database.');
		}

		return view('fileupload.edit', compact('fileupload'));
	}

	public function create(FileUpload $fileupload)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		return view('fileupload.create', compact('fileupload'));
	}

	public function store(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

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

			//create files upload folder, if not exists
			if (!file_exists(public_path() . '/files/')) {
				mkdir(public_path() . '/files/', 0777, true);
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
		$fileupload->file_name = $filename;
		$fileupload->file_description = $request->input('file_description');
		$fileupload->created_by = Auth::user()->id;
		$fileupload->save();

		//Move file to files folder
		$file->move(public_path() . '/files/', $filename);

		return Redirect::route('fileupload.index')->with('message', 'File uploaded');
	}

	public function update(FileUpload $fileupload, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'file_description' => 'required'
		]);

		$fileupload->update($request->all());
		return Redirect::route('fileupload.show', $fileupload->slug)->with('message', 'File details updated.');
	}

	public function destroy(FileUpload $fileupload)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//check if not exists
		if (file_exists(public_path() . '/files/' . $fileupload->file_name)) {
			//remove file from upload folder
			unlink('/' . base_path() . '/public/files/' . $fileupload->file_name);
		}

		$fileupload->delete();
		return Redirect::route('fileupload.index')->with('message', 'File deleted.');
	}
}

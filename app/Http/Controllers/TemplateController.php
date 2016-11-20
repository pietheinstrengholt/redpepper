<?php

namespace App\Http\Controllers;
use App\AuthService;
use App\ChangeRequest;
use App\DraftField;
use App\DraftRequirement;
use App\DraftTechnical;
use App\Helpers\ActivityLog;
use App\Http\Controllers\Controller;
use App\Requirement;
use App\Section;
use App\Subject;
use App\Technical;
use App\TechnicalDescription;
use App\TechnicalSource;
use App\TechnicalType;
use App\Template;
use App\TemplateColumn;
use App\TemplateRow;
use App\User;
use App\UserRights;
use Auth;
use Gate;
use Illuminate\Http\Request;
use Redirect;
use Session;
use Validator;

class TemplateController extends Controller
{
	protected $authService;

    public function __construct(AuthService $authService)
    {
       $this->authService = $authService;
    }

	//function to show template
	public function show(Subject $subject, Section $section, Template $template, Request $request)
	{
		//check if visible is set to false and user is a guest
		if (Gate::denies('see-nonvisible-content') && $template->visible == "False") {
			abort(403, 'Unauthorized action. This template hasn\'t been published yet.');
		}

		//check if id property exists
		if (!$template->id) {
			abort(403, 'This template no longer exists in the database.');
		}

		//check if id property exists
		if (!$section->id) {
			abort(403, 'The section where this template belongs to no longer exists in the database.');
		}

		//set empty search value
		$searchvalue = 'empty';

		//if both row and column are set, return combination, else only row or column
		if ($request->has('row') && $request->has('column')) {
			$searchvalue = "column" . $request->input('column') . "-row" . $request->input('row');
		} else if ($request->has('row')) {
			$searchvalue = "row" . $request->input('row');
		} else if ($request->has('column')) {
			$searchvalue = "column" . $request->input('column');
		}

		$disabledFields = $this->getDisabledFields($template);
		$propertyFields = $this->getPropertyFields($template);
		$technicaltype = TechnicalType::where('id', $template->type_id)->first();
		$descriptions = TechnicalDescription::where('type_id', $template->type_id)->orderBy('content', 'asc')->get();

		//get parent and children
		if (Gate::allows('see-nonvisible-content')) {
			$children = $template->children()->orderBy('template_name', 'asc')->get();
		} else {
			$children = $template->children()->orderBy('template_name', 'asc')->where('visible', '<>' , 'False')->get();
		}
		$children = $children->sortBy('template_name', SORT_NATURAL);

		//determine if the row_reference field is used, check on Null and Empty
		$emptyReferences = TemplateRow::where('template_id', $template->id)->where('row_reference', '=', null)->orWhere(function ($query) use ($template) {
			$query->where('template_id', $template->id)->where('row_reference', '=', '');
		})->get();

		//eager load additional content
		$template->load('section.subject', 'rows', 'columns', 'children', 'parent');

		return view('templates.show', compact('template', 'disabledFields', 'propertyFields', 'searchvalue', 'technicaltype', 'descriptions', 'emptyReferences'));
	}

	//function to disabled fields
	public function getDisabledFields(Template $template)
	{
		//check if id property exists
		if (!$template->id) {
			abort(403, 'This template no longer exists in the database.');
		}

		$disabledFields = Requirement::where('template_id', $template->id)->where('content_type', 'disabled')->get();

		//Create new arrays to restructure result
		$arraydisabled=array();
		//Restructure array
		if (!empty($disabledFields)) {
			foreach ($disabledFields as $disabledField) {
				$rowname = $disabledField->row_code;
				$columnname = $disabledField->column_code;
				$field = 'column' . trim($columnname) . '-' . 'row' . trim($rowname);
				$arraydisabled[$field] = $disabledField->content_type;
			}
		}

		return $arraydisabled;
	}

	//function to retrieve property2 fields
	public function getPropertyFields(Template $template)
	{
		//check if id property exists
		if (!$template->id) {
			abort(403, 'This template no longer exists in the database.');
		}

		$propertyFields = Requirement::where('template_id', $template->id)->where('content_type', 'property2')->get();

		//Create new arrays to restructure result
		$arrayproperty=array();
		//Restructure array
		if (!empty($propertyFields)) {
			foreach ($propertyFields as $propertyField) {
				$rowname = $propertyField->row_code;
				$columnname = $propertyField->column_code;
				$field = 'column' . trim($columnname) . '-' . 'row' . trim($rowname);
				$arrayproperty[$field] = $propertyField->content;
			}
		}

		return $arrayproperty;
	}

	//function to edit template
	public function edit(Subject $subject, Section $section, Template $template, Request $request)
	{
		//guests are not allowed to change templates
		if (Auth::user()->cant('update-section', $template->section)) {
			abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$template->id) {
			abort(403, 'This template no longer exists in the database.');
		}

		//retrieve list with sections via the AuthService Model and not by using Auth::user()->sections;
		$sectionlist = $this->authService->getSectionsList();
		$sections = Section::whereIn('id', $sectionlist)->orderBy('section_name', 'asc')->get();

		$types = TechnicalType::orderBy('type_name', 'asc')->get();
		//get non child templates wihtin section and not equal to own template id
		$templates = Template::orderBy('template_name', 'asc')->where('section_id', $template->section_id)->where('id', '!=', $template->id)->where('parent_id', null)->orWhere(function ($query) use ($template) {
			$query->where('section_id', $template->section_id)->where('id', '!=', $template->id)->where('parent_id', 0);
		})->get();

		$templates = $templates->sortBy('template_name', SORT_NATURAL);

		//validate if user can update section (see AuthServiceProvider)
		if ($request->user()->can('update-section', $section)) {
			return view('templates.edit', compact('subject','sections', 'section', 'template','templates','types'));
		} else {
			abort(403, 'Unauthorized action.');
		}
	}

	public function create(Subject $subject, Section $section, Template $template, Request $request)
	{
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action.');
		}

		//retrieve list with sections via the AuthService Model and not by using Auth::user()->sections;
		$sectionlist = $this->authService->getSectionsList();
		$sections = Section::whereIn('id', $sectionlist)->orderBy('section_name', 'asc')->get();

		//use default value to select from dropdown
		if (!empty($section)) {
			//get non child templates within section
			$templates = Template::orderBy('template_name', 'asc')->where('section_id', $section->id)->where('parent_id', null)->orWhere(function ($query) use ($section) {
				$query->where('section_id', $section->id)->where('parent_id', 0);
			})->get();
			$templates = $templates->sortBy('template_name', SORT_NATURAL);
		} else {
			$templates = null;
			$section = null;
		}

		return view('templates.create', compact('subject','sections','section','templates'));
	}

	//function to create new template
	public function newtemplate(Request $request)
	{
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'template_name' => 'required|min:4',
			'template_shortdesc' => 'required|min:4',
			'visible' => 'required',
			'section_id' => 'required'
		]);

		//validate when type is template
		if ($request->input('template-type') == "template") {
			$this->validate($request, [
				'inputrows' => 'required|numeric',
				'inputcolumns' => 'required|numeric'
			]);

			if ($request->input('inputrows') == 0 || $request->input('inputcolumns') == 0) {
				abort(403, 'Error: Template should not contain any zero values.');
			}
		}

		if ($request->isMethod('post')) {

			$section = Section::findOrFail($request->input('section_id'));

			$template = new Template;
			$template->section_id = $section->id;
			$template->parent_id = $request->input('parent_id');
			$template->template_name = $request->input('template_name');
			$template->template_shortdesc = $request->input('template_shortdesc');
			$template->template_longdesc = $request->input('template_longdesc');
			$template->visible = $request->input('visible');
			$template->template_type = $request->input('template_type');
			$template->created_by = Auth::user()->id;
			$template->save();

			//only create rows and columns if a valid value for both is given
			if ($request->input('inputrows') > 0 && $request->input('inputcolumns') > 0) {

				$inputrows = $request->input('inputrows');
				$inputcolumns = $request->input('inputcolumns');

				//add tempate rows to database
				$i = 1;
				while ($i <= $inputrows) {
					$j = $i;
					if (strlen($j) == "1") {
						$j = "0" . $j * 10;
					} else {
						$j = $j * 10;
					}

					$row = new TemplateRow;
					$row->template_id = $template->id;
					$row->row_num = $i;
					$row->row_code = $j;
					$row->row_description = 'description row number ' . $j;
					$row->row_reference = '';
					$row->save();
					$i++;
				}

				//add tempate columns to database
				$i = 1;
				while ($i <= $inputcolumns) {
					$j = $i;
					if (strlen($j) == "1") {
						$j = "0" . $j * 10;
					} else {
						$j = $j * 10;
					}
					$column = new TemplateColumn;
					$column->template_id = $template->id;
					$column->column_num = $i;
					$column->column_code = $j;
					$column->column_description = 'description column number ' . $j;
					$column->save();
					$i++;
				}
			}

			//log activity
			ActivityLog::submit("Template " . $template->template_name . " was created.");

		}
		return Redirect::to('/subjects/' . $section->subject_id . '/sections/' . $section->id . '/templates/' . $template->id);
	}

	//function to structure template
	public function structure(Subject $subject, Section $section, Template $template)
	{
		//check if id property exists
		if (!$template->id) {
			abort(403, 'This template no longer exists in the database.');
		}

		//guests are not allowed to change templates
		if (Auth::user()->cant('update-section', $template->section)) {
			abort(403, 'Unauthorized action.');
		}

		//get disabled fields
		$disabledFields = $this->getDisabledFields($template);

		return view('templates.structure', compact('template', 'disabledFields'));
	}

	//function to structure template
	public function changestructure(Subject $subject, Section $section, Template $template, Request $request)
	{
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}

		//check if id property exists
		if (!$template->id) {
			abort(403, 'This template no longer exists in the database.');
		}

		if ($request->isMethod('post')) {

			if ($request->has('template_id') && $request->has('section_id')) {

				//update column numbers
				if ($request->has('colnum')) {
					foreach($request->input('colnum') as $key => $value) {
						if (!empty($value)) {
							TemplateColumn::where('template_id', $request->input('template_id'))->where('column_code', $key)->update(['column_code' => $value]);
							Requirement::where('template_id', $request->input('template_id'))->where('column_code', $key)->update(['column_code' => $value]);
						}
					}
				}

				//update column desc
				if ($request->has('coldesc')) {
					foreach($request->input('coldesc') as $key => $value) {
						if (!empty($value)) {
							TemplateColumn::where('template_id', $request->input('template_id'))->where('column_code', $key)->update(['column_description' => $value]);
						}
					}
				}

				//update row numbers
				if ($request->has('rownum')) {
					foreach($request->input('rownum') as $key => $value) {
						if (!empty($value)) {
							TemplateRow::where('template_id', $request->input('template_id'))->where('row_code', $key)->update(['row_code' => $value]);
							Requirement::where('template_id', $request->input('template_id'))->where('row_code', $key)->update(['row_code' => $value]);
						}
					}
				}

				//update row desc
				if ($request->has('rowdesc')) {
					foreach($request->input('rowdesc') as $key => $value) {
						if (!empty($value)) {
							TemplateRow::where('template_id', $request->input('template_id'))->where('row_code', $key)->update(['row_description' => $value]);
						}
					}
				}

				//update row desc
				if ($request->has('row_property')) {
					foreach($request->input('row_property') as $key => $value) {
						if (!empty($value)) {
							TemplateRow::where('template_id', $request->input('template_id'))->where('row_code', $key)->update(['row_property' => $value]);
						}
					}
				}

				//delete disabled cells
				Requirement::where('template_id', $request->input('template_id'))->where('content_type', 'disabled')->delete();
				if ($request->has('options')) {
					foreach($request->input('options') as $disabled){
						//split options into row and column
						list($before, $after) = explode('-row', $disabled, 2);
						$column_code = str_ireplace("column", "", "$before");
						$row_code = $after;
						//create new disabled field
						$Requirement = new Requirement;
						$Requirement->template_id = $request->input('template_id');
						$Requirement->row_code = $row_code;
						$Requirement->column_code = $column_code;
						$Requirement->content_type = 'disabled';
						$Requirement->save();
					}
				}

				//update changerequests, technical and fields properties
				if ($request->has('colnum')) {
					foreach($request->input('colnum') as $columnkey => $columnvalue) {
						if (!empty($columnvalue)) {
							if ($request->has('rownum')) {
								foreach($request->input('rownum') as $rowkey => $rowvalue) {
									if (!empty($rowvalue)) {
										Requirement::where('template_id', $request->input('template_id'))->where('row_code', $rowkey)->where('column_code', $columnkey)->where('content_type', '!=' , 'disabled')->update(['row_code' => $rowvalue, 'column_code' => $columnvalue]);
										Technical::where('template_id', $request->input('template_id'))->where('row_code', $rowkey)->where('column_code', $columnkey)->update(['row_code' => $rowvalue, 'column_code' => $columnvalue]);
										ChangeRequest::where('template_id', $request->input('template_id'))->where('row_code', $rowkey)->where('column_code', $columnkey)->update(['row_code' => $rowvalue, 'column_code' => $columnvalue]);
									}
								}
							}
						}
					}
				}

				//log activity
				ActivityLog::submit("Template \"" . $template->template_name . "\" was updated.");

			}
		}
		return Redirect::route('subjects.sections.show', array($template->section->subject, $template->section))->with('message', 'Template structure updated.');
	}

	//function to add new template
	public function store(Subject $subject, Section $section)
	{
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}

		//validate input form
		$this->validate($request, [
			'template_name' => 'required|min:4',
			'template_shortdesc' => 'required|min:4'
		]);

		$input = $request->all();
		$input['section_id'] = $section->id;
		$template = Template::create($input);

		//log activity
		ActivityLog::submit("Template \"" . $template->template_name . "\" was created.");

		return Redirect::route('sections.show', $section->id)->with('message', 'Template created.');
	}

	//function to update template
	public function update(Subject $subject, Section $section, Template $template, Request $request)
	{
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}

		//validate input form
		$this->validate($request, [
			'template_name' => 'required|min:4',
			'template_shortdesc' => 'required|min:4'
		]);

		//log activity
		ActivityLog::submit("Template \"" . $template->template_name . "\" was updated.");

		$input = array_except($request->all(), '_method');
		$template->update($input);
		return Redirect::route('subjects.sections.templates.show', [$subject->id, $section->id, $template->id])->with('message', 'Template updated.');
	}

	//function to delete template
	public function destroy(Subject $subject, Section $section, Template $template, Request $request)
	{
		if ($request->user()->can('update-section', $section)) {

			if (Auth::user()->role == "builder" && $template->visible == "True") {
				abort(403, 'Unauthorized action. A template can only be deleted when set to non visible. Please change the template properties to be set to hidden for guest users and try again.');
			}

			//remove all related template content
			TemplateRow::where('template_id', $template->id)->delete();
			TemplateColumn::where('template_id', $template->id)->delete();
			Requirement::where('template_id', $template->id)->delete();
			Technical::where('template_id', $template->id)->delete();
			ChangeRequest::where('template_id', $template->id)->delete();
			//TODO: use for each procedure to also delete children content
			Template::where('parent_id', $template->id)->delete();

			//log activity
			ActivityLog::submit("Template \"" . $template->template_name . "\" was deleted.");

			//delete template
			$template->delete();
			return Redirect::route('subjects.sections.show', [$subject->id, $section->id])->with('message', 'Template deleted.');
		} else {
			abort(403, 'Unauthorized action. You don\'t have rights to this template or section');
		}
	}

	//content for the pop-up
	//TODO: use route
	public function getCellContent(Request $request)
	{
		if ($request->has('template_id') && $request->has('cell_id')) {
			$template = Template::findOrFail($request->input('template_id'));
		} else {
			abort(404, 'Content cannot be found with invalid arguments.');
		}

		//split input into row and column
		list($before, $after) = explode('-row', $_GET['cell_id'], 2);
		$column_code = str_ireplace("column", "", "$before");
		$row_code = $after;

		return view('templates.cell', [
			'template' => $template,
			'row' => TemplateRow::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->firstOrFail(),
			'column' => TemplateColumn::where('template_id', $request->input('template_id'))->where('column_code', $column_code)->firstOrFail(),
			'regulation_row' => Requirement::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->where('column_code', null)->where('content_type', 'regulation')->first(),
			'interpretation_row' => Requirement::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->where('column_code', null)->where('content_type', 'interpretation')->first(),
			'regulation_column' => Requirement::where('template_id', $request->input('template_id'))->where('column_code', $column_code)->where('row_code', null)->where('content_type', 'regulation')->first(),
			'interpretation_column' => Requirement::where('template_id', $request->input('template_id'))->where('column_code', $column_code)->where('row_code', null)->where('content_type', 'interpretation')->first(),
			'technical' => Technical::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->where('column_code', $column_code)->get(),
			'field_regulation' => Requirement::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->where('column_code', $column_code)->where('content_type', 'regulation')->first(),
			'field_interpretation' => Requirement::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->where('column_code', $column_code)->where('content_type', 'interpretation')->first(),
			'field_property1' => Requirement::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->where('column_code', $column_code)->where('content_type', 'property1')->first(),
			'field_property2' => Requirement::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->where('column_code', $column_code)->where('content_type', 'property2')->first()
		]);
	}

	public function manual(Subject $subject, Section $section, Template $template)
	{
		//check if id property exists
		if (!$template->id) {
			abort(403, 'This template no longer exists in the database.');
		}

		//eager load additional content
		$template->load('section.subject', 'rows', 'columns', 'requirements');

		$technical = Technical::where('template_id', $template->id)->orderBy('row_code', 'asc')->orderBy('column_code', 'asc')->get();

		return view('templates.manual', compact('template','technical'));
	}

	public function imageUpload(Request $request)
	{
		//create upload folder, if not exists
		if (!file_exists(public_path() . '/img/upload/')) {
			mkdir(public_path() . '/img/upload/', 0777, true);
		}

		//upload image with random string
		$file = $request->file('imagefile');
		$extension = $file->getClientOriginalExtension();

		$validExtensions = array("jpeg", "jpg", "png", "gif");

		if (in_array(strtolower($extension), $validExtensions)) {
			$random = str_random(10);
			$file->move(public_path() . '/img/upload/', $random . '.' . $extension);
			$file_path = str_replace("/public/index.php","",url('')) . '/public/img/upload/' . $random . '.' . $extension;
			return view('imageupload.image-upload', compact('file_path'));
		} else {
			$error = "An error occurred while processing the image. Unknown extension type.";
			return view('imageupload.image-upload', compact('error'));
		}
	}
}

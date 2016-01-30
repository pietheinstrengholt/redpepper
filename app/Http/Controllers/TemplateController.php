<?php

namespace App\Http\Controllers;
use App\ChangeRequest;
use App\DraftField;
use App\DraftRequirement;
use App\DraftTechnical;
use App\Events\TemplateCreated;
use App\Events\TemplateUpdated;
use App\Events\TemplateDeleted;
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
use App\UserRights;
use Auth;
use Event;
use Gate;
use Illuminate\Http\Request;
use Redirect;
use Session;
use Validator;

class TemplateController extends Controller
{
	//function to retrieve section rights based on user id
	public function sectionRights() {

		if (Auth::guest()) {
			abort(403, 'Unauthorized action.');
		}

		if (!(Auth::user()->role == "builder" || Auth::user()->role == "superadmin" || Auth::user()->role == "admin")) {
			abort(403, 'Unauthorized action. You are not allowed to make changes for this section.');
		}

		if (Auth::user()->role == "builder" || Auth::user()->role == "admin") {

			$userrights = UserRights::where('username_id', Auth::user()->id)->get();

			$sectionRights = array();
			$userrights = $userrights->toArray();
			if (!empty($userrights)) {
				foreach ($userrights as $userright) {
					array_push($sectionRights,$userright['section_id']);
				}
			}
			$sections = Section::whereIn('id', $sectionRights)->where('visible','True')->orderBy('section_name', 'asc')->get();
		}
		if (Auth::user()->role == "superadmin") {
			$sections = Section::orderBy('section_name', 'asc')->get();
		}
		return $sections;
	}
	
	//function to retrieve section rights based on user id
	public function sectionArray() {
		
		//set empty var
		$sectionRights = array();

		//check if user is logged on
		if (!(Auth::guest())) {
			
			//admin and builder have restrictions based on userrights
			if (Auth::user()->role == "admin" || Auth::user()->role == "builder") {

				$userrights = UserRights::where('username_id', Auth::user()->id)->get();

				$userrights = $userrights->toArray();
				if (!empty($userrights)) {
					foreach ($userrights as $userright) {
						array_push($sectionRights,$userright['section_id']);
					}
				}
			}
			
		}

		//return sections
		return $sectionRights;
	}

	//function to show template
	public function show(Section $section, Template $template, Request $request)
	{
		//check if visible is set to false and user is a guest
		if (Auth::guest() && $template->visible == "False") {
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
		return view('templates.show', compact('section', 'template', 'disabledFields', 'propertyFields', 'searchvalue'));
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
	public function edit(Section $section, Template $template)
	{
		//guests are not allowed to change templates
		if (Auth::guest()) {
            abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$template->id) {
			abort(403, 'This template no longer exists in the database.');
		}

		//retrieve list with sections based on user id and user role
		$sectionArray = $this->sectionArray();
		
		//retrieve list with sections based on user id and user role
		$sections = $this->sectionRights();
		
		//builder and admin are only permitted to upload to own sections. when builder the template should be published
		if ((Auth::user()->role == "admin" && in_array($section->id, $sectionArray)) || Auth::user()->role == "superadmin" || (Auth::user()->role == "builder" && in_array($section->id, $sectionArray) && $template->visible == "True")) {
			return view('templates.edit', compact('sections', 'section', 'template'));
		} else {
			abort(403, 'Unauthorized action.');
		}
	}

	public function create()
	{
		//retrieve list with sections based on user id and user role
		$sections = $this->sectionRights();

		if (empty($sections)) {
			abort(403, 'Unauthorized action. You don\'t have access to any sections');
		}

		return view('templates.create', compact('sections'));
	}


	//function to structure template
	public function structure($id)
	{
		$template = Template::findOrFail($id);

		//retrieve list with sections based on user id and user role
		$sectionArray = $this->sectionArray();
		
		//builder and admin are only permitted to upload to own sections. when builder the template should be published
		if ((Auth::user()->role == "admin" && in_array($section->id, $sectionArray)) || Auth::user()->role == "superadmin" || (Auth::user()->role == "builder" && in_array($section->id, $sectionArray) && $template->visible == "True")) {
			$disabledFields = $this->getDisabledFields($template);
			return view('templates.structure', compact('section', 'template', 'disabledFields'));
		} else {
			abort(403, 'Unauthorized action.');
		}


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
			'inputrows' => 'required|numeric',
			'inputcolumns' => 'required|numeric',
			'template_name' => 'required|min:4',
			'template_shortdesc' => 'required|min:4',
			'section_id' => 'required'
		]);

		if ($request->isMethod('post')) {

			$template = new Template;
			$template->section_id = $request->input('section_id');
			$template->template_name = $request->input('template_name');
			$template->template_shortdesc = $request->input('template_shortdesc');
			$template->template_longdesc = $request->input('template_longdesc');
			$template->visible = 'No';
			$template->save();

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

			//log Event
			Event::fire(new TemplateCreated($template));

		}
		return Redirect::to('/templatestructure/' . $template->id);
	}

	//function to structure template
	public function changestructure(Request $request)
	{
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}

		$template = Template::findOrFail($request->input('template_id'));

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

				//log Event
				Event::fire(new TemplateUpdated($template));

			}
		}
		return Redirect::route('sections.show', $request->input('section_id'))->with('message', 'Template structure updated.');
	}

	//function to add new template
	public function store(Section $section)
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

		//log Event
		Event::fire(new TemplateCreated($template));

		return Redirect::route('sections.show', $section->id)->with('message', 'Template created.');
	}

	//function to update template
	public function update(Section $section, Template $template, Request $request)
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

		//log Event
		Event::fire(new TemplateUpdated($template));

		$input = array_except($request->all(), '_method');
		$template->update($input);
		return Redirect::route('sections.templates.show', [$section->id, $template->id])->with('message', 'Template updated.');
	}

	//function to delete template
	public function destroy(Section $section, Template $template)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//remove all related template content
		TemplateRow::where('template_id', $template->id)->delete();
		TemplateColumn::where('template_id', $template->id)->delete();
		Requirement::where('template_id', $template->id)->delete();
		Technical::where('template_id', $template->id)->delete();
		ChangeRequest::where('template_id', $template->id)->delete();

		//log Event
		Event::fire(new TemplateDeleted($template));

		//delete template
		$template->delete();
		return Redirect::route('sections.show', $section->id)->with('message', 'Template deleted.');
	}

	//content for the pop-up
	public function getCellContent(Request $request)
	{
		if (!($request->has('template_id') && $request->has('cell_id'))) {
			abort(404, 'Content cannot be found with invalid arguments.');
		}

		//split input into row and column
		list($before, $after) = explode('-row', $_GET['cell_id'], 2);
		$column_code = str_ireplace("column", "", "$before");
		$row_code = $after;

		return view('templates.cell', [
			'template' => Template::find($request->input('template_id')),
			'row' => TemplateRow::where('template_id', $request->input('template_id'))->where('row_code', $row_code)->first(),
			'column' => TemplateColumn::where('template_id', $request->input('template_id'))->where('column_code', $column_code)->first(),
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

}

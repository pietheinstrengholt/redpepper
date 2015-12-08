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
use App\Http\Controllers\Controller;

use Gate;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Input;
use Redirect;
use Validator;
use Session;

class TemplateController extends Controller
{
	//function to show template
    public function show(Section $section, Template $template, Request $request)
    {
	
		//check if visible is set to false and user is a guest
		if (Auth::guest() && $template->visible == "False") {
			abort(403, 'Unauthorized action.');
		}	
	
		//set empty search value
		$searchvalue = 'empty';
		
		//return field_id, e.g. R-010 as row010 or column010
		if ($request->has('field_id')) {
			//replace R- or C- with row or column
			if (preg_match('/R-/', $request->input('field_id'))) {
				$searchvalue = str_ireplace("R-", "row", $request->input('field_id'));
			}
			
			if (preg_match('/C-/', $request->input('field_id'))) {
				$searchvalue = str_ireplace("C-", "column", $request->input('field_id'));
			}
		}
		
		//if both row and column are set, return combination, else only row or column
		if ($request->has('row') && $request->has('column')) {
			$searchvalue = "column" . $request->input('column') . "-row" . $request->input('row');
		} else if ($request->has('row')) {
			$searchvalue = "row" . $request->input('row');
		} else if ($request->has('column')) {
			$searchvalue = "column" . $request->input('column');
		}
		
		$disabledFields = $this->getDisabledFields($template);
		return view('templates.show', compact('section', 'template', 'disabledFields', 'searchvalue'));
    }
	
	//function to disabled fields
	public function getDisabledFields(Template $template)
	{
		$disabledFields = TemplateField::where('template_id', $template->id)->where('property', 'disabled')->get();
		
		//Create new arrays to restructure result
		$arraydisabled=array();
		//Restructure array
		if (!empty($disabledFields)) {
			foreach ($disabledFields as $disabledField) {
				$rowname = $disabledField->row_code;
				$columnname = $disabledField->column_code;
				$field = 'column' . trim($columnname) . '-' . 'row' . trim($rowname);
				$arraydisabled[$field] = $disabledField->property;
			}
		}
		
		return $arraydisabled;
	}		
	
	//function to edit template
	public function edit(Section $section, Template $template)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
	
		$sections = Section::orderBy('section_name', 'asc')->get();
		return view('templates.edit', compact('sections', 'section', 'template'));
	}
	
	public function create()
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
		
		$sections = Section::orderBy('section_name', 'asc')->get();
		return view('templates.create', compact('sections'));
	}
	
	//function to create new template
	public function newtemplate(Request $request)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

		if ($request->isMethod('post')) {
			if (!($request->has('template_name') && $request->has('template_shortdesc') && $request->has('section_id') && $request->has('inputcolumns') && $request->has('inputrows'))) {
				abort(403, 'Cannot create template. Some argument are missing.');
			}
				
			if (!is_numeric($request->input('inputcolumns')) || !is_numeric($request->input('inputrows'))) {
				abort(403, 'Argument for row and column numbers is not numeric.');
			}
			
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
	
		if ($request->isMethod('post')) {
			
			if ($request->has('template_id') && $request->has('section_id')) {

				//update column numbers
				if ($request->has('colnum')) {
					foreach($request->input('colnum') as $key => $value) {
						if (!empty($value)) {
							TemplateColumn::where('template_id', $request->input('template_id'))->where('column_code', $key)->update(['column_code' => $value]);
							Requirement::where('template_id', $request->input('template_id'))->where('field_id', 'C-' . $key)->update(['field_id' => 'C-' . $value]);
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
							Requirement::where('template_id', $request->input('template_id'))->where('field_id', 'R-' . $key)->update(['field_id' => 'R-' . $value]);
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
				TemplateField::where('template_id', $request->input('template_id'))->where('property', 'disabled')->delete();
				if ($request->has('options')) {
					foreach($request->input('options') as $disabled){
						//split options into row and column
						list($before, $after) = explode('-row', $disabled, 2);
						$column_code = str_ireplace("column", "", "$before");
						$row_code = $after;
						//create new disabled field
						$TemplateField = new TemplateField;
						$TemplateField->template_id = $request->input('template_id');
						$TemplateField->row_code = $row_code;
						$TemplateField->column_code = $column_code;
						$TemplateField->property = 'disabled';
						$TemplateField->save();
					}
				}
				
				//update changerequests, technical and fields properties
				if ($request->has('colnum')) {
					foreach($request->input('colnum') as $columnkey => $columnvalue) {
						if (!empty($columnvalue)) {
							if ($request->has('rownum')) {
								foreach($request->input('rownum') as $rowkey => $rowvalue) {
									if (!empty($rowvalue)) {
										TemplateField::where('template_id', $request->input('template_id'))->where('row_code', $rowkey)->where('column_code', $columnkey)->where('property', '!=' , 'disabled')->update(['row_code' => $rowvalue, 'column_code' => $columnvalue]);
										Technical::where('template_id', $request->input('template_id'))->where('row_code', $rowkey)->where('column_code', $columnkey)->update(['row_code' => $rowvalue, 'column_code' => $columnvalue]);
										ChangeRequest::where('template_id', $request->input('template_id'))->where('row_code', $rowkey)->where('column_code', $columnkey)->update(['row_code' => $rowvalue, 'column_code' => $columnvalue]);
									}
								}
							}
						}
					}
				}
				
			}
		}
		return Redirect::route('sections.show', $request->input('section_id'))->with('message', 'Template created.');
	}
	
	//function to structure template
	public function structure($id)
	{
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}
	
		$template = Template::find($id);
		$disabledFields = $this->getDisabledFields($template);
		return view('templates.structure', compact('section', 'template', 'disabledFields'));
	}	
	
	//function to add new template
	public function store(Section $section)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }		
	
		$input = Input::all();
		$input['section_id'] = $section->id;
		Template::create( $input );
		return Redirect::route('sections.show', $section->id)->with('message', 'Template created.');
	}
	
	//function to update template
	public function update(Section $section, Template $template)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
	
		$input = array_except(Input::all(), '_method');
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
		TemplateField::where('template_id', $template->id)->delete();
		Requirement::where('template_id', $template->id)->delete();
		Technical::where('template_id', $template->id)->delete();
		ChangeRequest::where('template_id', $template->id)->delete();	
	
		//delete template
		$template->delete();
		return Redirect::route('sections.show', $section->id)->with('message', 'Template deleted.');
	}
	
	//content for the pop-up
    public function getCellContent()
    {
		//abort if template_id and cell_id are not set
		if (empty($_GET['template_id']) || empty($_GET['cell_id'])) {
			abort(404, 'Content cannot be found with invalid arguments.');
		}
		
		//split input into row and column
		list($before, $after) = explode('-row', $_GET['cell_id'], 2);
		$column_code = str_ireplace("column", "", "$before");
		$row_code = $after;				
		
		return view('templates.cell', [
			'template' => Template::find($_GET['template_id']),
			'row' => TemplateRow::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->first(),
			'column' => TemplateColumn::where('template_id', $_GET['template_id'])->where('column_code', $column_code)->first(),
			'regulation_row' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'R-' . $row_code)->where('content_type', 'regulation')->first(),
			'regulation_column' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'C-' . $column_code)->where('content_type', 'regulation')->first(),
			'interpretation_row' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'R-' . $row_code)->where('content_type', 'interpretation')->first(),
			'interpretation_column' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'C-' . $column_code)->where('content_type', 'interpretation')->first(),
			'technical' => Technical::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->get(),
			'field_regulation' => TemplateField::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->where('property', 'regulation')->get(),
			'field_interpretation' => TemplateField::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->where('property', 'interpretation')->get(),
			'field_property1' => TemplateField::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->where('property', 'property1')->get(),
			'field_property2' => TemplateField::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->where('property', 'property2')->get()
		]);
		
    }
	
}

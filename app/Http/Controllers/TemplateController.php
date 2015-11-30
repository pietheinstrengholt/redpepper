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
use Illuminate\Http\Request;
use Input;
use Redirect;
use Validator;
use Session;

class TemplateController extends Controller
{
	//function to show template
    public function show(Section $section, Template $template)
    {
		$disabledFields = $this->getDisabledFields($template);
		return view('templates.show', compact('section', 'template','disabledFields'));
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
				$rowname = $disabledField->row_name;
				$columnname = $disabledField->column_name;
				$field = 'column' . trim($columnname) . '-' . 'row' . trim($rowname);
				$arraydisabled[$field] = $disabledField->property;
			}
		}
		
		return $arraydisabled;
	}		
	
	//function to edit template
	public function edit(Section $section, Template $template)
	{
		return view('templates.edit', compact('section', 'template'));
	}
	
	//function to add new template
	public function store(Section $section)
	{
		$input = Input::all();
		$input['section_id'] = $section->id;
		Template::create( $input );
		return Redirect::route('sections.show', $section->id)->with('message', 'Template created.');
	}
	
	//function to update template
	public function update(Request $request)
	{
		$input = array_except(Input::all(), '_method');
		$template->update($input);
		return Redirect::route('sections.templates.show', [$section->id, $template->id])->with('message', 'Template updated.');
	}
	
	//function to delete template
	public function destroy(Section $section, Template $template)
	{
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
		$columnnum = str_ireplace("column", "", "$before");
		$rownum = $after;
		
		return view('templates.cell', [
			'template' => Template::find($_GET['template_id']),
			'row' => TemplateRow::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->first(),
			'column' => TemplateColumn::where('template_id', $_GET['template_id'])->where('column_name', $columnnum)->first(),
			'regulation_row' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'R-' . $rownum)->where('content_type', 'regulation')->first(),
			'regulation_column' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'C-' . $columnnum)->where('content_type', 'regulation')->first(),
			'interpretation_row' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'R-' . $rownum)->where('content_type', 'interpretation')->first(),
			'interpretation_column' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'C-' . $columnnum)->where('content_type', 'interpretation')->first(),
			'technical' => Technical::where('template_id', $_GET['template_id'])->where('row_num', $rownum)->where('col_num', $columnnum)->get(),
			'field_regulation' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'regulation')->get(),
			'field_interpretation' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'interpretation')->get(),
			'field_property1' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'property1')->get(),
			'field_property2' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'property2')->get()
		]);
		
    }
	
}

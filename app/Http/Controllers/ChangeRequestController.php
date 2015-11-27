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

use App\Libraries\FineDiff;

class ChangeRequestController extends Controller
{

    public function index()
    {
		$changerequests = ChangeRequest::all();
		return view('changerequests.index', compact('changerequests'));
    }
	
	//set compare granularity level
	public function compare($from, $to) {
		$diff = FineDiff::getDiffOpcodes($from, $to, FineDiff::$sentenceGranularity);
		$diffHTML = FineDiff::renderDiffToHTMLFromOpcodes($from, $diff);
		return $diffHTML;
	}
	//set compare granularity level
	public function compare_character($from, $to) {
		$diff = FineDiff::getDiffOpcodes($from, $to, FineDiff::$characterGranularity);
		$diffHTML = FineDiff::renderDiffToHTMLFromOpcodes($from, $diff);
		return $diffHTML;
	}
	//set compare granularity level
	public function compare_word($from, $to) {
		$diff = FineDiff::getDiffOpcodes($from, $to, FineDiff::$wordGranularity);
		$diffHTML = FineDiff::renderDiffToHTMLFromOpcodes($from, $diff);
		return $diffHTML;
	}	
	
	public function edit(ChangeRequest $changerequest)
	{
		
		//get current content
		$current_requirement_row = Requirement::where('template_id', $changerequest->template_id)->where('field_id', 'R-' . $changerequest->row_number)->first();
		$current_requirement_column = Requirement::where('template_id', $changerequest->template_id)->where('field_id', 'C-' . $changerequest->column_number)->first();
		$current_technical = Technical::where('template_id', $changerequest->template_id)->where('row_num', $changerequest->row_number)->where('col_num', $changerequest->column_number)->get();
		$current_field_legal_desc = TemplateField::where('template_id', $changerequest->template_id)->where('row_name', $changerequest->row_number)->where('column_name', $changerequest->column_number)->where('property', 'legal_desc')->first();
		$current_field_interpretation_desc = TemplateField::where('template_id', $changerequest->template_id)->where('row_name', $changerequest->row_number)->where('column_name', $changerequest->column_number)->where('property', 'interpretation_desc')->first();
		$current_field_property1 = TemplateField::where('template_id', $changerequest->template_id)->where('row_name', $changerequest->row_number)->where('column_name', $changerequest->column_number)->where('property', 'property1')->first();
		$current_field_property2 = TemplateField::where('template_id', $changerequest->template_id)->where('row_name', $changerequest->row_number)->where('column_name', $changerequest->column_number)->where('property', 'property2')->first();
		
		//get draft content
		$draft_requirement = DraftRequirement::where('changerequest_id', $changerequest->id)->first();
		$draft_technical = DraftTechnical::where('changerequest_id', $changerequest->id)->get();
		$draft_field_legal_desc = DraftField::where('changerequest_id', $changerequest->id)->where('property', 'legal_desc')->first();
		$draft_field_interpretation_desc = DraftField::where('changerequest_id', $changerequest->id)->where('property', 'interpretation_desc')->first();
		$draft_field_property1 = DraftField::where('changerequest_id', $changerequest->id)->where('property', 'property1')->first();
		$draft_field_property2 = DraftField::where('changerequest_id', $changerequest->id)->where('property', 'property2')->first();		
		
		//perform comparison
		$changerequest->legal_requirement_row = $this->compare_word($current_requirement_row['legal_desc'], $draft_requirement['row_legal_desc']);
		$changerequest->interpretation_requirement_row = $this->compare_word($current_requirement_row['interpretation_desc'], $draft_requirement['row_interpretation_desc']);
		$changerequest->legal_requirement_column = $this->compare_word($current_requirement_column['legal_desc'], $draft_requirement['column_legal_desc']);
		$changerequest->interpretation_requirement_column = $this->compare_word($current_requirement_column['interpretation_desc'], $draft_requirement['column_interpretation_desc']);
		$changerequest->legal_desc = $this->compare($current_field_legal_desc['content'],$draft_field_legal_desc['content']);
		$changerequest->interpretation_desc = $this->compare($current_field_interpretation_desc['content'],$draft_field_interpretation_desc['content']);
		$changerequest->field_property1 = $this->compare($current_field_property1['content'],$draft_field_property1['content']);
		$changerequest->field_property2 = $this->compare($current_field_property2['content'],$draft_field_property2['content']);
		
		$current_technical_string = "";
		foreach ($current_technical as $current_technical_row) {
			$str = $current_technical_row->source->source_name . " - " . $current_technical_row->type->type_name . " " . $current_technical_row->content . " " . $current_technical_row->description . "\n";
			$current_technical_string = $current_technical_string . $str;
		}

		$draft_technical_string = "";
		foreach ($draft_technical as $draft_technical_row) {
			$str = $draft_technical_row->source->source_name . " - " . $draft_technical_row->type->type_name . " " . $draft_technical_row->content . " " . $draft_technical_row->description . "\n";
			$draft_technical_string = $draft_technical_string . $str;
		}
		
		$changerequest->technical = $this->compare($current_technical_string,$draft_technical_string);
		
		return view('changerequests.edit', [
			'changerequest' => $changerequest,
			'template' => Template::find($changerequest->template_id),
			'template_row' => TemplateRow::where('template_id', $changerequest->template_id)->where('row_name', $changerequest->row_number)->first(),
			'template_column' => TemplateColumn::where('template_id', $changerequest->template_id)->where('column_name', $changerequest->column_number)->first()
		]);
		
	}

	public function store()
	{
		$input = Input::all();
		ChangeRequest::create( $input );
		return Redirect::route('changerequests.index')->with('message', 'Changerequest created');
	}
	 
	public function update(ChangeRequest $type)
	{
		$input = array_except(Input::all(), '_method');
		$type->update($input);
		return Redirect::route('changerequests.show', $type->slug)->with('message', 'Changerequest updated.');
	}
	 
	public function destroy(ChangeRequest $type)
	{
		$type->delete();
		return Redirect::route('changerequests.index')->with('message', 'Type deleted.');
	}

	public function submit(Request $request)
	{
		if ($request->isMethod('post')) {

			$changerequest = new ChangeRequest;
			$changerequest->template_id = $request->input('template_id');
			$changerequest->row_number = $request->input('row_name');
			$changerequest->column_number = $request->input('column_name');
			$changerequest->creator_id = 1;
			$changerequest->status = 'draft';
			$changerequest->save();
			
			$draftrequirement = new DraftRequirement;
			$draftrequirement->changerequest_id = $changerequest->id;
			$draftrequirement->row_legal_desc = $request->input('requirement_row_legal_desc');
			$draftrequirement->row_interpretation_desc = $request->input('requirement_row_interpretation_desc');
			$draftrequirement->column_legal_desc = $request->input('requirement_column_legal_desc');
			$draftrequirement->column_interpretation_desc = $request->input('requirement_column_interpretation_desc');			
			$draftrequirement->save();
			
			$technicalData = Input::get('technical');
			
			foreach($technicalData as $technical) {
			
				if(isset($technical['action'])) { $row_action = $technical['action']; } else { $row_action = NULL; }
				if(isset($technical['hidden'])) { $row_hidden = $technical['hidden']; } else { $row_hidden = "no"; }
				if(isset($technical['source_id'])) { $row_system = $technical['source_id']; } else { $row_system = 0; }
			
				if ($row_action != "delete" && $row_system != 0 && $row_hidden == "no") {
				
					$drafttechnical = new DraftTechnical;
					$drafttechnical->changerequest_id = $changerequest->id;
					$drafttechnical->source_id = $technical['source_id'];
					$drafttechnical->type_id = $technical['type_id']; 
					$drafttechnical->content = $technical['content'];
					$drafttechnical->description = $technical['description'];
					$drafttechnical->save();
				
				}
			
			}
			
			if ($request->has('field_property1')) {
				$draftfield = new DraftField;
				$draftfield->changerequest_id = $changerequest->id;
				$draftfield->property = 'property1';
				$draftfield->content = $request->input('field_property1');
				$draftfield->save();
			}
			
			if ($request->has('field_property2')) {
				$draftfield = new DraftField;
				$draftfield->changerequest_id = $changerequest->id;
				$draftfield->property = 'property2';
				$draftfield->content = $request->input('field_property2');
				$draftfield->save();
			}

			if ($request->has('field_legal_desc')) {
				$draftfield = new DraftField;
				$draftfield->changerequest_id = $changerequest->id;
				$draftfield->property = 'legal_desc';
				$draftfield->content = $request->input('field_legal_desc');
				$draftfield->save();
			}

			if ($request->has('field_interpretation_desc')) {
				$draftfield = new DraftField;
				$draftfield->changerequest_id = $changerequest->id;
				$draftfield->property = 'interpretation_desc';
				$draftfield->content = $request->input('field_interpretation_desc');
				$draftfield->save();
			}			
			
			//redirect back to template page
			return Redirect::route('sections.templates.show', [$request->input('section_id'), $request->input('template_id')])->with('message', 'Change request submitted for review.');

		}
	}
	
    public function create()
    {
		//abort if template_id and cell_id are not set
		if (empty($_GET['template_id']) || empty($_GET['cell_id'])) {
			abort(404, 'Content cannot be found with invalid arguments.');
		}
		
		//split input into row and column
		list($before, $after) = explode('-row', $_GET['cell_id'], 2);
		$columnnum = str_ireplace("column", "", "$before");
		$rownum = $after;

		return view('templates.cell-update', [
			'template' => Template::find($_GET['template_id']),
			'row' => TemplateRow::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->first(),
			'column' => TemplateColumn::where('template_id', $_GET['template_id'])->where('column_name', $columnnum)->first(),
			'requirement_row' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'R-' . $rownum)->first(),
			'requirement_column' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'C-' . $columnnum)->first(),
			'technical' => Technical::where('template_id', $_GET['template_id'])->where('row_num', $rownum)->where('col_num', $columnnum)->get(),
			'types' => TechnicalType::all(),
			'sources' => TechnicalSource::all(),
			'field_legal_desc' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'legal_desc')->first(),
			'field_interpretation_desc' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'interpretation_desc')->first(),
			'field_property1' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'property1')->first(),
			'field_property2' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'property2')->first()
		]);
		
    }	
	
}

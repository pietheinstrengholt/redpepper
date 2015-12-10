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
use Validator;
use Session;

use App\Libraries\FineDiff;

use Gate;
use App\User;
use App\UserRights;
use Auth;

class ChangeRequestController extends Controller
{
	public function templateRights($id) {
	
		$userrights = UserRights::where('username_id', $id)->get();
		
		$templatesRights = array();
		$userrights = $userrights->toArray();
		if (!empty($userrights)) {
			foreach ($userrights as $userright) {
				$templates = Template::where('section_id', $userright['section_id'])->get();
				if (!empty($templates)) {
					foreach ($templates as $template) {
						array_push($templatesRights,$template->id);
					}
				}
			}
		}
		return $templatesRights;
	}
	
    public function index()
    {
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}
		
		//contributors and builders can only see own submitted changes
		if (Auth::user()->role == "contributor" || Auth::user()->role == "builder") {
			$changerequests = ChangeRequest::where('creator_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
		}
		
		if (Auth::user()->role == "admin" || Auth::user()->role == "reviewer") {
			$templateList = $this->templateRights(Auth::user()->id);
			$changerequests = ChangeRequest::whereIn('template_id', $templateList)->orderBy('created_at', 'desc')->get();
		}
		
		//superadmin users can see all
		if (Auth::user()->role == "superadmin") {
			$changerequests = ChangeRequest::orderBy('created_at', 'desc')->get();
		}

		return view('changerequests.index', compact('changerequests'));
    }	
	
    public function create()
    {
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}
		
		//abort if template_id and cell_id are not set
		if (empty($_GET['template_id']) || empty($_GET['cell_id'])) {
			abort(404, 'Content cannot be found with invalid arguments.');
		}
		
		//check if the admin, builder or reviewer user has the correct section rights
		if (Auth::user()->role == "admin" || Auth::user()->role == "builder" || Auth::user()->role == "contributor") {
			$templateList = $this->templateRights(Auth::user()->id);
			if (!in_array($_GET['template_id'], $templateList)) {
				abort(403, 'Unauthorized action. You don\'t have access to this template or section');
			}
		}
		
		//split input into row and column
		list($before, $after) = explode('-row', $_GET['cell_id'], 2);
		$columnnum = str_ireplace("column", "", "$before");
		$rownum = $after;		
		
		if (Auth::user()->role == "reviewer" || Auth::user()->role == "guest" || Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');		
		}

		return view('templates.cell-update', [
			'template' => Template::find($_GET['template_id']),
			'row' => TemplateRow::where('template_id', $_GET['template_id'])->where('row_code', $rownum)->first(),
			'column' => TemplateColumn::where('template_id', $_GET['template_id'])->where('column_code', $columnnum)->first(),
			'regulation_row' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'R-' . $rownum)->where('content_type', 'regulation')->first(),
			'regulation_column' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'C-' . $columnnum)->where('content_type', 'regulation')->first(),
			'interpretation_row' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'R-' . $rownum)->where('content_type', 'interpretation')->first(),
			'interpretation_column' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'C-' . $columnnum)->where('content_type', 'interpretation')->first(),
			'technical' => Technical::where('template_id', $_GET['template_id'])->where('row_code', $rownum)->where('column_code', $columnnum)->get(),
			'types' => TechnicalType::all(),
			'sources' => TechnicalSource::all(),
			'field_regulation' => TemplateField::where('template_id', $_GET['template_id'])->where('row_code', $rownum)->where('column_code', $columnnum)->where('property', 'regulation')->first(),
			'field_interpretation' => TemplateField::where('template_id', $_GET['template_id'])->where('row_code', $rownum)->where('column_code', $columnnum)->where('property', 'interpretation')->first(),
			'field_property1' => TemplateField::where('template_id', $_GET['template_id'])->where('row_code', $rownum)->where('column_code', $columnnum)->where('property', 'property1')->first(),
			'field_property2' => TemplateField::where('template_id', $_GET['template_id'])->where('row_code', $rownum)->where('column_code', $columnnum)->where('property', 'property2')->first()
		]);
    }
	
    public function cleanup()
    {
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }		
		ChangeRequest::where('status', 'rejected')->orWhere('status', 'approved')->delete();
		return Redirect::route('changerequests.index')->with('message', 'Cleanup performed.');
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
	
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');		
		}
		
		if (Auth::user()->role == "guest") {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');		
		}
	
		//set allowed to change to yes
		$allowedToChange = "yes";
	
		if (Auth::user()->role == "admin" || Auth::user()->role == "builder" || Auth::user()->role == "reviewer") {
			if ($changerequest->creator_id == Auth::user()->id) {
				$allowedToChange = "no";
			}
			
			$templateList = $this->templateRights(Auth::user()->id);
			
			if (!in_array($changerequest->template_id, $templateList)) {
				abort(403, 'Unauthorized action. You don\'t have access to this template or section');
			}
		}
		
		if (Auth::user()->role == "builder" || Auth::user()->role == "contributor") {
			$allowedToChange = "no";
		}
		
		//get current content
		$current_regulation_row = Requirement::where('template_id', $changerequest->template_id)->where('field_id', 'R-' . $changerequest->row_code)->where('content_type', 'regulation')->first();
		$current_interpretation_row = Requirement::where('template_id', $changerequest->template_id)->where('field_id', 'R-' . $changerequest->row_code)->where('content_type', 'interpretation')->first();
		$current_regulation_column = Requirement::where('template_id', $changerequest->template_id)->where('field_id', 'C-' . $changerequest->column_code)->where('content_type', 'regulation')->first();
		$current_interpretation_column = Requirement::where('template_id', $changerequest->template_id)->where('field_id', 'C-' . $changerequest->column_code)->where('content_type', 'interpretation')->first();		
		$current_technical = Technical::where('template_id', $changerequest->template_id)->where('row_code', $changerequest->row_code)->where('column_code', $changerequest->column_code)->orderBy('content', 'asc')->get();
		$current_field_regulation = TemplateField::where('template_id', $changerequest->template_id)->where('row_code', $changerequest->row_code)->where('column_code', $changerequest->column_code)->where('property', 'regulation')->first();
		$current_field_interpretation = TemplateField::where('template_id', $changerequest->template_id)->where('row_code', $changerequest->row_code)->where('column_code', $changerequest->column_code)->where('property', 'interpretation')->first();
		$current_field_property1 = TemplateField::where('template_id', $changerequest->template_id)->where('row_code', $changerequest->row_code)->where('column_code', $changerequest->column_code)->where('property', 'property1')->first();
		$current_field_property2 = TemplateField::where('template_id', $changerequest->template_id)->where('row_code', $changerequest->row_code)->where('column_code', $changerequest->column_code)->where('property', 'property2')->first();
		
		//get draft content
		$draft_regulation_row = DraftRequirement::where('changerequest_id', $changerequest->id)->where('field_id', 'R-' . $changerequest->row_code)->where('content_type', 'regulation')->first();
		$draft_interpretation_row = DraftRequirement::where('changerequest_id', $changerequest->id)->where('field_id', 'R-' . $changerequest->row_code)->where('content_type', 'interpretation')->first();
		$draft_regulation_column = DraftRequirement::where('changerequest_id', $changerequest->id)->where('field_id', 'C-' . $changerequest->column_code)->where('content_type', 'regulation')->first();
		$draft_interpretation_column = DraftRequirement::where('changerequest_id', $changerequest->id)->where('field_id', 'C-' . $changerequest->column_code)->where('content_type', 'interpretation')->first();
		$draft_technical = DraftTechnical::where('changerequest_id', $changerequest->id)->orderBy('content', 'asc')->get();
		$draft_field_regulation = DraftField::where('changerequest_id', $changerequest->id)->where('property', 'regulation')->first();
		$draft_field_interpretation = DraftField::where('changerequest_id', $changerequest->id)->where('property', 'interpretation')->first();
		$draft_field_property1 = DraftField::where('changerequest_id', $changerequest->id)->where('property', 'property1')->first();
		$draft_field_property2 = DraftField::where('changerequest_id', $changerequest->id)->where('property', 'property2')->first();		
		
		//perform comparison
		$changerequest->regulation_row = $this->compare_word($current_regulation_row['content'], $draft_regulation_row['content']);
		$changerequest->interpretation_row = $this->compare_word($current_interpretation_row['content'], $draft_interpretation_row['content']);
		$changerequest->regulation_column = $this->compare_word($current_regulation_column['content'], $draft_regulation_column['content']);
		$changerequest->interpretation_column = $this->compare_word($current_interpretation_column['content'], $draft_interpretation_column['content']);
		$changerequest->field_regulation = $this->compare($current_field_regulation['content'],$draft_field_regulation['content']);
		$changerequest->field_interpretation = $this->compare($current_field_interpretation['content'],$draft_field_interpretation['content']);
		$changerequest->field_property1 = $this->compare($current_field_property1['content'],$draft_field_property1['content']);
		$changerequest->field_property2 = $this->compare($current_field_property2['content'],$draft_field_property2['content']);
		
		$current_technical_string = "";
		if (!empty($current_technical)) {
			foreach ($current_technical as $current_technical_row) {
				$str = $current_technical_row->source->source_name . " - " . $current_technical_row->type->type_name . " " . $current_technical_row->content . " " . $current_technical_row->description . "\n";
				$current_technical_string = $current_technical_string . $str;
			}
		}

		$draft_technical_string = "";
		if (!empty($draft_technical)) {
			foreach ($draft_technical as $draft_technical_row) {
			
				if (!is_object($draft_technical_row->source)) {
					abort(403, 'The source name no longer exists in the database. Unable to view change request.');
				}
				
				if (!is_object($draft_technical_row->type)) {
					abort(403, 'The type name no longer exists in the database. Unable to view change request.');
				}
			
				$str = $draft_technical_row->source->source_name . " - " . $draft_technical_row->type->type_name . " " . $draft_technical_row->content . " " . $draft_technical_row->description . "\n";
				$draft_technical_string = $draft_technical_string . $str;
			}
		}
		
		$changerequest->technical = $this->compare($current_technical_string,$draft_technical_string);
		
		return view('changerequests.edit', [
			'changerequest' => $changerequest,
			'template' => Template::find($changerequest->template_id),
			'template_row' => TemplateRow::where('template_id', $changerequest->template_id)->where('row_code', $changerequest->row_code)->first(),
			'template_column' => TemplateColumn::where('template_id', $changerequest->template_id)->where('column_code', $changerequest->column_code)->first(),
			'allowedToChange' => $allowedToChange
		]);
		
	}

	public function store()
	{
		$input = Input::all();
		ChangeRequest::create( $input );
		return Redirect::route('changerequests.index')->with('message', 'Changerequest created');
	}
	 
	public function update(Request $request)
	{
		//check if user is logged on
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');		
		}
	
		//validate input form
		$this->validate($request, [
			'changerequest_id' => 'required',
			'comment' => 'required'
		]);
	
		if ($request->isMethod('post')) {
	
			if ($request->has('changerequest_id')) {
			
				$changerequest_id = $request->input('changerequest_id');
				$ChangeRequest = ChangeRequest::find($changerequest_id);
				
				if ($ChangeRequest['status'] <> 'pending') {
					abort(403, 'Error: change request already processed!');
				}
			
				//update change request
				if ($request->input('change_type') == "rejected") {
					ChangeRequest::where('id', $changerequest_id)->update(['status' => 'rejected']);
					ChangeRequest::where('id', $changerequest_id)->update(['comment' => $request->input('comment')]);
				}
				
				//update change request
				if ($request->input('change_type') == "approved") {
					ChangeRequest::where('id', $changerequest_id)->update(['status' => 'approved']);
					ChangeRequest::where('id', $changerequest_id)->update(['comment' => $request->input('comment')]);
					
					//get draft content
					$DraftRegulation_row = DraftRequirement::where('changerequest_id', $changerequest_id)->where('field_id', 'R-' . $ChangeRequest->row_code)->where('content_type', 'regulation')->first();
					$DraftInterpretation_row = DraftRequirement::where('changerequest_id', $changerequest_id)->where('field_id', 'R-' . $ChangeRequest->row_code)->where('content_type', 'interpretation')->first();
					$DraftRegulation_column = DraftRequirement::where('changerequest_id', $changerequest_id)->where('field_id', 'C-' . $ChangeRequest->column_code)->where('content_type', 'regulation')->first();
					$DraftInterpretation_column = DraftRequirement::where('changerequest_id', $changerequest_id)->where('field_id', 'C-' . $ChangeRequest->column_code)->where('content_type', 'interpretation')->first();

					$DraftField_property1 = DraftField::where('changerequest_id', $changerequest_id)->where('property', 'property1')->first();
					$DraftField_property2 = DraftField::where('changerequest_id', $changerequest_id)->where('property', 'property2')->first();
					$DraftField_regulation = DraftField::where('changerequest_id', $changerequest_id)->where('property', 'regulation')->first();
					$DraftField_interpretation = DraftField::where('changerequest_id', $changerequest_id)->where('property', 'interpretation')->first();
					
					$DraftTechnical = DraftTechnical::where('changerequest_id', $changerequest_id)->get();
					
					if (!empty($ChangeRequest['template_id'])) {

						//get existing content
						$Regulation_row = Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'R-' . $ChangeRequest->row_code)->where('content_type', 'regulation')->first();
						$Interpretation_row = Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'R-' . $ChangeRequest->row_code)->where('content_type', 'interpretation')->first();
						$Regulation_column = Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'C-' . $ChangeRequest->column_code)->where('content_type', 'regulation')->first();
						$Interpretation_column = Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'C-' . $ChangeRequest->column_code)->where('content_type', 'interpretation')->first();
						
						$field_property1 = TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'property1')->first();
						$field_property2 = TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'property2')->first();
						$field_regulation = TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'regulation')->first();
						$field_interpretation = TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'interpretation')->first();
						$technical = Technical::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->get();

						
						//delete any existing if empty is proposed
						if (count($DraftRegulation_row) == 0) {
							Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'R-' . $ChangeRequest->row_code)->where('content_type', 'regulation')->delete();
						} else {
							//insert
							if (count($Regulation_row) == 0) {
								$Requirements = new Requirement;
								$Requirements->template_id = $ChangeRequest->template_id;
								$Requirements->field_id = 'R-' . $ChangeRequest->row_code;
								$Requirements->content_type = 'regulation';
								$Requirements->content = $DraftRegulation_row->content;
								$Requirements->save();
							//update
							} else {
								Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'R-' . $ChangeRequest->row_code)->where('content_type', 'regulation')->update(['content' => $DraftRegulation_row->content]);

								//submit existing content to archive table
								$HistoryRequirement = new HistoryRequirement;
								$HistoryRequirement->changerequest_id = $ChangeRequest->id;
								$HistoryRequirement->template_id = $ChangeRequest->template_id;
								$HistoryRequirement->row_code = $ChangeRequest->row_code;
								$HistoryRequirement->column_code = '';
								$HistoryRequirement->content_type = 'regulation';
								$HistoryRequirement->content = $Regulation_row->content;
								$HistoryRequirement->change_type = 'existing';
								$HistoryRequirement->created_by = $ChangeRequest->creator_id;
								$HistoryRequirement->submission_date = $ChangeRequest->created_at;
								$HistoryRequirement->approved_by = Auth::user()->id;
								$HistoryRequirement->save();
							}
							
							//submit new content to archive table
							$HistoryRequirement = new HistoryRequirement;
							$HistoryRequirement->changerequest_id = $ChangeRequest->id;
							$HistoryRequirement->template_id = $ChangeRequest->template_id;
							$HistoryRequirement->row_code = $ChangeRequest->row_code;
							$HistoryRequirement->column_code = '';
							$HistoryRequirement->content_type = 'regulation';
							$HistoryRequirement->content = $DraftRegulation_row->content;
							$HistoryRequirement->change_type = 'new';
							$HistoryRequirement->created_by = $ChangeRequest->creator_id;
							$HistoryRequirement->submission_date = $ChangeRequest->created_at;
							$HistoryRequirement->approved_by = Auth::user()->id;
							$HistoryRequirement->save();
							
						}
						
						//delete any existing if empty is proposed
						if (count($DraftInterpretation_row) == 0) {
							Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'R-' . $ChangeRequest->row_code)->where('content_type', 'interpretation')->delete();
						} else {
							//insert
							if (count($Interpretation_row) == 0) {
								$Requirements = new Requirement;
								$Requirements->template_id = $ChangeRequest->template_id;
								$Requirements->field_id = 'R-' . $ChangeRequest->row_code;
								$Requirements->content_type = 'interpretation';
								$Requirements->content = $DraftInterpretation_row->content;
								$Requirements->save();
							//update
							} else {
								Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'R-' . $ChangeRequest->row_code)->where('content_type', 'interpretation')->update(['content' => $DraftInterpretation_row->content]);
								
								//submit existing content to archive table
								$HistoryRequirement = new HistoryRequirement;
								$HistoryRequirement->changerequest_id = $ChangeRequest->id;
								$HistoryRequirement->template_id = $ChangeRequest->template_id;
								$HistoryRequirement->row_code = $ChangeRequest->row_code;
								$HistoryRequirement->column_code = '';
								$HistoryRequirement->content_type = 'interpretation';
								$HistoryRequirement->content = $Interpretation_row->content;
								$HistoryRequirement->change_type = 'existing';
								$HistoryRequirement->created_by = $ChangeRequest->creator_id;
								$HistoryRequirement->submission_date = $ChangeRequest->created_at;
								$HistoryRequirement->approved_by = Auth::user()->id;
								$HistoryRequirement->save();								
								
							}
							
							//submit new content to archive table
							$HistoryRequirement = new HistoryRequirement;
							$HistoryRequirement->changerequest_id = $ChangeRequest->id;
							$HistoryRequirement->template_id = $ChangeRequest->template_id;
							$HistoryRequirement->row_code = $ChangeRequest->row_code;
							$HistoryRequirement->column_code = '';
							$HistoryRequirement->content_type = 'interpretation';
							$HistoryRequirement->content = $DraftInterpretation_row->content;
							$HistoryRequirement->change_type = 'new';
							$HistoryRequirement->created_by = $ChangeRequest->creator_id;
							$HistoryRequirement->submission_date = $ChangeRequest->created_at;
							$HistoryRequirement->approved_by = Auth::user()->id;
							$HistoryRequirement->save();								
							
						}						

						//delete any existing if empty is proposed
						if (count($DraftRegulation_column) == 0) {
							Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'C-' . $ChangeRequest->column_code)->where('content_type', 'regulation')->delete();
						} else {
							//insert
							if (count($Regulation_column) == 0) {
								$Requirements = new Requirement;
								$Requirements->template_id = $ChangeRequest->template_id;
								$Requirements->field_id = 'C-' . $ChangeRequest->column_code;
								$Requirements->content_type = 'regulation';
								$Requirements->content = $DraftRegulation_column->content;
								$Requirements->save();
							//update
							} else {
								Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'C-' . $ChangeRequest->column_code)->where('content_type', 'regulation')->update(['content' => $DraftRegulation_column->content]);
								
								//submit existing content to archive table
								$HistoryRequirement = new HistoryRequirement;
								$HistoryRequirement->changerequest_id = $ChangeRequest->id;
								$HistoryRequirement->template_id = $ChangeRequest->template_id;
								$HistoryRequirement->row_code = '';
								$HistoryRequirement->column_code = $ChangeRequest->column_code;
								$HistoryRequirement->content_type = 'regulation';
								$HistoryRequirement->content = $Regulation_column->content;
								$HistoryRequirement->change_type = 'existing';
								$HistoryRequirement->created_by = $ChangeRequest->creator_id;
								$HistoryRequirement->submission_date = $ChangeRequest->created_at;
								$HistoryRequirement->approved_by = Auth::user()->id;
								$HistoryRequirement->save();									
							}
							
							//submit existing content to archive table
							$HistoryRequirement = new HistoryRequirement;
							$HistoryRequirement->changerequest_id = $ChangeRequest->id;
							$HistoryRequirement->template_id = $ChangeRequest->template_id;
							$HistoryRequirement->row_code = '';
							$HistoryRequirement->column_code = $ChangeRequest->column_code;
							$HistoryRequirement->content_type = 'regulation';
							$HistoryRequirement->content = $DraftRegulation_column->content;
							$HistoryRequirement->change_type = 'new';
							$HistoryRequirement->created_by = $ChangeRequest->creator_id;
							$HistoryRequirement->submission_date = $ChangeRequest->created_at;
							$HistoryRequirement->approved_by = Auth::user()->id;
							$HistoryRequirement->save();								
							
						}
						
						//delete any existing if empty is proposed
						if (count($DraftInterpretation_column) == 0) {
							Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'C-' . $ChangeRequest->column_code)->where('content_type', 'interpretation')->delete();
						} else {
							//insert
							if (count($Interpretation_column) == 0) {
								$Requirements = new Requirement;
								$Requirements->template_id = $ChangeRequest->template_id;
								$Requirements->field_id = 'C-' . $ChangeRequest->column_code;
								$Requirements->content_type = 'interpretation';
								$Requirements->content = $DraftInterpretation_column->content;
								$Requirements->save();
							//update
							} else {
								Requirement::where('template_id', $ChangeRequest->template_id)->where('field_id', 'C-' . $ChangeRequest->column_code)->where('content_type', 'interpretation')->update(['content' => $DraftInterpretation_column->content]);
								
								//submit existing content to archive table
								$HistoryRequirement = new HistoryRequirement;
								$HistoryRequirement->changerequest_id = $ChangeRequest->id;
								$HistoryRequirement->template_id = $ChangeRequest->template_id;
								$HistoryRequirement->row_code = '';
								$HistoryRequirement->column_code = $ChangeRequest->column_code;
								$HistoryRequirement->content_type = 'interpretation';
								$HistoryRequirement->content = $Interpretation_column->content;
								$HistoryRequirement->change_type = 'existing';
								$HistoryRequirement->created_by = $ChangeRequest->creator_id;
								$HistoryRequirement->submission_date = $ChangeRequest->created_at;
								$HistoryRequirement->approved_by = Auth::user()->id;
								$HistoryRequirement->save();									
							}
							
							//submit new content to archive table
							$HistoryRequirement = new HistoryRequirement;
							$HistoryRequirement->changerequest_id = $ChangeRequest->id;
							$HistoryRequirement->template_id = $ChangeRequest->template_id;
							$HistoryRequirement->row_code = '';
							$HistoryRequirement->column_code = $ChangeRequest->column_code;
							$HistoryRequirement->content_type = 'interpretation';
							$HistoryRequirement->content = $DraftInterpretation_column->content;
							$HistoryRequirement->change_type = 'new';
							$HistoryRequirement->created_by = $ChangeRequest->creator_id;
							$HistoryRequirement->submission_date = $ChangeRequest->created_at;
							$HistoryRequirement->approved_by = Auth::user()->id;
							$HistoryRequirement->save();								
							
						}

						//delete any existing if empty is proposed
						if (count($DraftField_property1) == 0) {
							TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'property1')->delete();
						} else {
							//insert
							if (count($field_property1) == 0) {
								$TemplateField = new TemplateField;
								$TemplateField->template_id = $ChangeRequest->template_id;
								$TemplateField->row_code = $ChangeRequest->row_code;
								$TemplateField->column_code = $ChangeRequest->column_code;
								$TemplateField->property = 'property1';
								$TemplateField->content = $DraftField_property1->content;
								$TemplateField->save();
							//update
							} else {
								TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'property1')->update(['content' => $DraftField_property1->content]);
								
								//submit existing content to archive table
								$HistoryRequirement = new HistoryRequirement;
								$HistoryRequirement->changerequest_id = $ChangeRequest->id;
								$HistoryRequirement->template_id = $ChangeRequest->template_id;
								$HistoryRequirement->row_code = $ChangeRequest->row_code;
								$HistoryRequirement->column_code = $ChangeRequest->column_code;
								$HistoryRequirement->content_type = 'property1';
								$HistoryRequirement->content = $field_property1->content;
								$HistoryRequirement->change_type = 'existing';
								$HistoryRequirement->created_by = $ChangeRequest->creator_id;
								$HistoryRequirement->submission_date = $ChangeRequest->created_at;
								$HistoryRequirement->approved_by = Auth::user()->id;
								$HistoryRequirement->save();
							}
							
							//submit new content to archive table
							$HistoryRequirement = new HistoryRequirement;
							$HistoryRequirement->changerequest_id = $ChangeRequest->id;
							$HistoryRequirement->template_id = $ChangeRequest->template_id;
							$HistoryRequirement->row_code = $ChangeRequest->row_code;
							$HistoryRequirement->column_code = $ChangeRequest->column_code;
							$HistoryRequirement->content_type = 'property1';
							$HistoryRequirement->content = $DraftField_property1->content;
							$HistoryRequirement->change_type = 'new';
							$HistoryRequirement->created_by = $ChangeRequest->creator_id;
							$HistoryRequirement->submission_date = $ChangeRequest->created_at;
							$HistoryRequirement->approved_by = Auth::user()->id;
							$HistoryRequirement->save();								
							
						}
						
						//delete any existing if empty is proposed
						if (count($DraftField_property2) == 0) {
							TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'property2')->delete();
						} else {
							//insert
							if (count($field_property2) == 0) {
								$TemplateField = new TemplateField;
								$TemplateField->template_id = $ChangeRequest->template_id;
								$TemplateField->row_code = $ChangeRequest->row_code;
								$TemplateField->column_code = $ChangeRequest->column_code;
								$TemplateField->property = 'property2';
								$TemplateField->content = $DraftField_property2->content;
								$TemplateField->save();
							//update
							} else {
								TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'property2')->update(['content' => $DraftField_property2->content]);
								
								//submit existing content to archive table
								$HistoryRequirement = new HistoryRequirement;
								$HistoryRequirement->changerequest_id = $ChangeRequest->id;
								$HistoryRequirement->template_id = $ChangeRequest->template_id;
								$HistoryRequirement->row_code = $ChangeRequest->row_code;
								$HistoryRequirement->column_code = $ChangeRequest->column_code;
								$HistoryRequirement->content_type = 'property2';
								$HistoryRequirement->content = $field_property2->content;
								$HistoryRequirement->change_type = 'existing';
								$HistoryRequirement->created_by = $ChangeRequest->creator_id;
								$HistoryRequirement->submission_date = $ChangeRequest->created_at;
								$HistoryRequirement->approved_by = Auth::user()->id;
								$HistoryRequirement->save();								
							}
							
							//submit new content to archive table
							$HistoryRequirement = new HistoryRequirement;
							$HistoryRequirement->changerequest_id = $ChangeRequest->id;
							$HistoryRequirement->template_id = $ChangeRequest->template_id;
							$HistoryRequirement->row_code = $ChangeRequest->row_code;
							$HistoryRequirement->column_code = $ChangeRequest->column_code;
							$HistoryRequirement->content_type = 'property2';
							$HistoryRequirement->content = $DraftField_property2->content;
							$HistoryRequirement->change_type = 'new';
							$HistoryRequirement->created_by = $ChangeRequest->creator_id;
							$HistoryRequirement->submission_date = $ChangeRequest->created_at;
							$HistoryRequirement->approved_by = Auth::user()->id;
							$HistoryRequirement->save();								
							
						}

						//delete any existing if empty is proposed
						if (count($DraftField_regulation) == 0) {
							TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'regulation')->delete();
						} else {
							//insert
							if (count($field_regulation) == 0) {
								$TemplateField = new TemplateField;
								$TemplateField->template_id = $ChangeRequest->template_id;
								$TemplateField->row_code = $ChangeRequest->row_code;
								$TemplateField->column_code = $ChangeRequest->column_code;
								$TemplateField->property = 'regulation';
								$TemplateField->content = $DraftField_regulation->content;
								$TemplateField->save();
							//update
							} else {
								TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'regulation')->update(['content' => $DraftField_regulation->content]);
								
								//submit existing content to archive table
								$HistoryRequirement = new HistoryRequirement;
								$HistoryRequirement->changerequest_id = $ChangeRequest->id;
								$HistoryRequirement->template_id = $ChangeRequest->template_id;
								$HistoryRequirement->row_code = $ChangeRequest->row_code;
								$HistoryRequirement->column_code = $ChangeRequest->column_code;
								$HistoryRequirement->content_type = 'regulation';
								$HistoryRequirement->content = $field_regulation->content;
								$HistoryRequirement->change_type = 'existing';
								$HistoryRequirement->created_by = $ChangeRequest->creator_id;
								$HistoryRequirement->submission_date = $ChangeRequest->created_at;
								$HistoryRequirement->approved_by = Auth::user()->id;
								$HistoryRequirement->save();									
							}
							
							//submit new content to archive table
							$HistoryRequirement = new HistoryRequirement;
							$HistoryRequirement->changerequest_id = $ChangeRequest->id;
							$HistoryRequirement->template_id = $ChangeRequest->template_id;
							$HistoryRequirement->row_code = $ChangeRequest->row_code;
							$HistoryRequirement->column_code = $ChangeRequest->column_code;
							$HistoryRequirement->content_type = 'regulation';
							$HistoryRequirement->content = $DraftField_regulation->content;
							$HistoryRequirement->change_type = 'new';
							$HistoryRequirement->created_by = $ChangeRequest->creator_id;
							$HistoryRequirement->submission_date = $ChangeRequest->created_at;
							$HistoryRequirement->approved_by = Auth::user()->id;
							$HistoryRequirement->save();							
							
						}

						//delete any existing if empty is proposed
						if (count($DraftField_interpretation) == 0) {
							TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'interpretation')->delete();
						} else {
							//insert
							if (count($field_interpretation) == 0) {
								$TemplateField = new TemplateField;
								$TemplateField->template_id = $ChangeRequest->template_id;
								$TemplateField->row_code = $ChangeRequest->row_code;
								$TemplateField->column_code = $ChangeRequest->column_code;
								$TemplateField->property = 'interpretation';
								$TemplateField->content = $DraftField_interpretation->content;
								$TemplateField->save();
							//update
							} else {
								TemplateField::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('property', 'interpretation')->update(['content' => $DraftField_interpretation->content]);
								
								//submit existing content to archive table
								$HistoryRequirement = new HistoryRequirement;
								$HistoryRequirement->changerequest_id = $ChangeRequest->id;
								$HistoryRequirement->template_id = $ChangeRequest->template_id;
								$HistoryRequirement->row_code = $ChangeRequest->row_code;
								$HistoryRequirement->column_code = $ChangeRequest->column_code;
								$HistoryRequirement->content_type = 'interpretation';
								$HistoryRequirement->content = $field_interpretation->content;
								$HistoryRequirement->change_type = 'existing';
								$HistoryRequirement->created_by = $ChangeRequest->creator_id;
								$HistoryRequirement->submission_date = $ChangeRequest->created_at;
								$HistoryRequirement->approved_by = Auth::user()->id;
								$HistoryRequirement->save();									
							}
							
							//submit new content to archive table
							$HistoryRequirement = new HistoryRequirement;
							$HistoryRequirement->changerequest_id = $ChangeRequest->id;
							$HistoryRequirement->template_id = $ChangeRequest->template_id;
							$HistoryRequirement->row_code = $ChangeRequest->row_code;
							$HistoryRequirement->column_code = $ChangeRequest->column_code;
							$HistoryRequirement->content_type = 'interpretation';
							$HistoryRequirement->content = $DraftField_interpretation->content;
							$HistoryRequirement->change_type = 'new';
							$HistoryRequirement->created_by = $ChangeRequest->creator_id;
							$HistoryRequirement->submission_date = $ChangeRequest->created_at;
							$HistoryRequirement->approved_by = Auth::user()->id;
							$HistoryRequirement->save();
						}
						
						
						//delete any existing content
						if (count($DraftTechnical) == 0) {
							Technical::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->delete();
						} else {
						
							//submit existing technical content to archive 
							if (count($technical) != 0) {
								foreach($technical as $techexistingrow) {
									//submit new content to archive table
									$HistoryTechnical = new HistoryTechnical;
									$HistoryTechnical->changerequest_id = $ChangeRequest->id;
									$HistoryTechnical->template_id = $ChangeRequest->template_id;
									$HistoryTechnical->row_code = $ChangeRequest->row_code;
									$HistoryTechnical->column_code = $ChangeRequest->column_code;
									$HistoryTechnical->type_id = $techexistingrow->type_id;
									$HistoryTechnical->source_id = $techexistingrow->source_id;		
									$HistoryTechnical->content = $techexistingrow->content;
									$HistoryTechnical->description = $techexistingrow->description;
									$HistoryTechnical->change_type = 'existing';
									$HistoryTechnical->created_by = $ChangeRequest->creator_id;
									$HistoryTechnical->submission_date = $ChangeRequest->created_at;
									$HistoryTechnical->approved_by = Auth::user()->id;
									$HistoryTechnical->save();
								}
							}
						
							Technical::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->delete();
							foreach($DraftTechnical as $Technicalrow) {
								$Technical = new Technical;
								$Technical->template_id = $ChangeRequest->template_id;
								$Technical->row_code = $ChangeRequest->row_code;
								$Technical->column_code = $ChangeRequest->column_code;
								$Technical->type_id = $Technicalrow->type_id;
								$Technical->source_id = $Technicalrow->source_id;
								$Technical->content = $Technicalrow->content;
								$Technical->description = $Technicalrow->description;
								$Technical->save();
								
								//submit new content to archive table
								$HistoryTechnical = new HistoryTechnical;
								$HistoryTechnical->changerequest_id = $ChangeRequest->id;
								$HistoryTechnical->template_id = $ChangeRequest->template_id;
								$HistoryTechnical->row_code = $ChangeRequest->row_code;
								$HistoryTechnical->column_code = $ChangeRequest->column_code;
								$HistoryTechnical->type_id = $Technicalrow->type_id;
								$HistoryTechnical->source_id = $Technicalrow->source_id;		
								$HistoryTechnical->content = $Technicalrow->content;
								$HistoryTechnical->description = $Technicalrow->description;
								$HistoryTechnical->change_type = 'new';
								$HistoryTechnical->created_by = $ChangeRequest->creator_id;
								$HistoryTechnical->submission_date = $ChangeRequest->created_at;
								$HistoryTechnical->approved_by = Auth::user()->id;
								$HistoryTechnical->save();
							}
						}
					}
				}
			}
		}
		return Redirect::route('changerequests.index')->with('message', 'Changerequest updated.');
	}
	 
	public function destroy(ChangeRequest $type)
	{
		$type->delete();
		return Redirect::route('changerequests.index')->with('message', 'ChangeRequest deleted.');
	}

	public function submit(Request $request)
	{
		//validate input form
		$this->validate($request, [
			'template_id' => 'required',
			'row_code' => 'required',
			'column_code' => 'required'
		]);
	
		if ($request->isMethod('post')) {

			$changerequest = new ChangeRequest;
			$changerequest->template_id = $request->input('template_id');
			$changerequest->row_code = $request->input('row_code');
			$changerequest->column_code = $request->input('column_code');
			$changerequest->creator_id = Auth::user()->id;
			$changerequest->status = 'pending';
			$changerequest->save();

			if ($request->has('regulation_row')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $changerequest->id;
				$draftrequirement->field_id = 'R-' . $request->input('row_code');
				$draftrequirement->content_type = 'regulation';
				$draftrequirement->content = $request->input('regulation_row');		
				$draftrequirement->save();
			}
			
			if ($request->has('interpretation_row')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $changerequest->id;
				$draftrequirement->field_id = 'R-' . $request->input('row_code');
				$draftrequirement->content_type = 'interpretation';
				$draftrequirement->content = $request->input('interpretation_row');		
				$draftrequirement->save();
			}
			
			if ($request->has('regulation_column')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $changerequest->id;
				$draftrequirement->field_id = 'C-' . $request->input('column_code');
				$draftrequirement->content_type = 'regulation';
				$draftrequirement->content = $request->input('regulation_column');		
				$draftrequirement->save();
			}
			
			if ($request->has('interpretation_column')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $changerequest->id;
				$draftrequirement->field_id = 'C-' . $request->input('column_code');
				$draftrequirement->content_type = 'interpretation';
				$draftrequirement->content = $request->input('interpretation_column');		
				$draftrequirement->save();
			}			
			
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

			if ($request->has('field_interpretation')) {
				$draftfield = new DraftField;
				$draftfield->changerequest_id = $changerequest->id;
				$draftfield->property = 'regulation';
				$draftfield->content = $request->input('field_interpretation');
				$draftfield->save();
			}

			if ($request->has('field_regulation')) {
				$draftfield = new DraftField;
				$draftfield->changerequest_id = $changerequest->id;
				$draftfield->property = 'interpretation';
				$draftfield->content = $request->input('field_regulation');
				$draftfield->save();
			}			
			
			//redirect back to template page
			return Redirect::route('sections.templates.show', [$request->input('section_id'), $request->input('template_id')])->with('message', 'Change request submitted for review.');

		}
	}
	
}

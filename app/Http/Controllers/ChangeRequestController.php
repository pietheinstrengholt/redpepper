<?php

namespace App\Http\Controllers;
use App\ChangeRequest;
use App\DraftRequirement;
use App\DraftTechnical;
use App\Events\ChangeRequestCreated;
use App\Events\ChangeRequestApproved;
use App\Events\ChangeRequestRejected;
use App\Events\ChangeRequestDeleted;
use App\HistoryRequirement;
use App\HistoryTechnical;
use App\Http\Controllers\Controller;
use App\Libraries\FineDiff;
use App\Requirement;
use App\Section;
use App\Technical;
use App\TechnicalSource;
use App\TechnicalType;
use App\Template;
use App\TemplateColumn;
use App\TemplateRow;
use App\User;
use App\Helper;
use App\UserRights;
use Auth;
use Event;
use Gate;
use Illuminate\Http\Request;
use Redirect;
use Session;
use Validator;

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
		if (Auth::user()->role == "contributor") {
			$changerequests = ChangeRequest::where('creator_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(15);
		}

		if (Auth::user()->role == "admin" || Auth::user()->role == "reviewer" || Auth::user()->role == "builder") {
			$templateList = $this->templateRights(Auth::user()->id);
			$changerequests = ChangeRequest::whereIn('template_id', $templateList)->orderBy('created_at', 'desc')->paginate(15);
		}

		//superadmin users can see all
		if (Auth::user()->role == "superadmin") {
			$changerequests = ChangeRequest::orderBy('created_at', 'desc')->paginate(15);
		}

		//check if any change request are found
		if (empty($changerequests)) {
			abort(403, 'No changerequests found, based on user credentials and submitted content by other users.');
		}

		return view('changerequests.index', ['changerequests' => $changerequests]);
    }

    public function create(Request $request)
    {
		//exit when user is a guest
		if (Auth::guest()) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}
		
		if ($request->has('template_id') && $request->has('cell_id')) {
			$template = Template::findOrFail($request->input('template_id'));			
		} else {
			abort(404, 'Content cannot be found with invalid arguments.');			
		}
		
		//exit when the user has no permission
		if ($request->user()->cannot('create-changerequest', $template)) {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}

		//split input into row and column
		list($before, $after) = explode('-row', $_GET['cell_id'], 2);
		$column_code = str_ireplace("column", "", "$before");
		$row_code = $after;
		
		//build list with users with section_id rights
		$userrights = UserRights::where('section_id', $template->section_id)->select('username_id')->get();
		$userList = array();
		
		if (!empty($userrights)) {
			foreach($userrights as $userright) {
				array_push($userList,$userright->username_id);
			}
		}
		
		//build list with superadmins
		$superadmins = User::orderBy('firstname', 'asc')->where('id', '<>', Auth::user()->id)->where('role', 'superadmin')->get();
		if (!empty($superadmins)) {
			foreach($superadmins as $superadmin) {
				array_push($userList,$superadmin->id);
			}
		}
		
		//query the users based on the roles, list with user rights and superadmins
		$approvers = User::orderBy('firstname', 'asc')->where('id', '<>', Auth::user()->id)->whereIn('id', $userList)->whereIn('role', array("superadmin","builder","admin","reviewer"))->get();

		return view('templates.cell-update', [
			'template' => $template,
			'row' => TemplateRow::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->firstOrFail(),
			'column' => TemplateColumn::where('template_id', $_GET['template_id'])->where('column_code', $column_code)->firstOrFail(),
			'regulation_row' => Requirement::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', null)->where('content_type', 'regulation')->first(),
			'regulation_column' => Requirement::where('template_id', $_GET['template_id'])->where('column_code', $column_code)->where('row_code', null)->where('content_type', 'regulation')->first(),
			'interpretation_row' => Requirement::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', null)->where('content_type', 'interpretation')->first(),
			'interpretation_column' => Requirement::where('template_id', $_GET['template_id'])->where('column_code', $column_code)->where('row_code', null)->where('content_type', 'interpretation')->first(),
			'technical' => Technical::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->get(),
			'types' => TechnicalType::all(),
			'sources' => TechnicalSource::all(),
			'field_regulation' => Requirement::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->where('content_type', 'regulation')->first(),
			'field_interpretation' => Requirement::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->where('content_type', 'interpretation')->first(),
			'field_property1' => Requirement::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->where('content_type', 'property1')->first(),
			'field_property2' => Requirement::where('template_id', $_GET['template_id'])->where('row_code', $row_code)->where('column_code', $column_code)->where('content_type', 'property2')->first(),
			'approvers' => $approvers
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

	public function edit(ChangeRequest $ChangeRequest)
	{
		//check if id property exists, else exit
		if (!$ChangeRequest->id) {
			abort(403, 'Change request no longer exists in the database.');
		}
		
		//set allowed to change to no
		$allowedToChange = "no";

		if (!(Auth::guest())) {

			//check for admin, builder, reviewer if not own submitted changerequest is reviewed
			if (Auth::user()->role == "admin" || Auth::user()->role == "builder" || Auth::user()->role == "reviewer") {
				
				//set allowed to change to yes
				$allowedToChange = "yes";
				
				//user are not allowed to approve own changes
				//TODO: add setting to allow superadmin to approve own changes
				if ($ChangeRequest->creator_id == Auth::user()->id) {
					$allowedToChange = "no";
				}

				//check if users have section rights
				$templateList = $this->templateRights(Auth::user()->id);
				if (!in_array($ChangeRequest->template_id, $templateList)) {
					$allowedToChange = "no";
				}
			}
			
			//superadmin has also rights to change
			if (Auth::user()->role == "superadmin") {
				
				//set allowed to change to yes
				$allowedToChange = "yes";
			}
		}
		
		if ($ChangeRequest->status <> 'approved') {
			//get current content
			$current_regulation_row = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'regulation')->first();
			$current_interpretation_row = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'interpretation')->first();
			$current_regulation_column = Requirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'regulation')->first();
			$current_interpretation_column = Requirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'interpretation')->first();
			$current_technical = Technical::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->orderBy('content', 'asc')->get();
			$current_field_regulation = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'regulation')->first();
			$current_field_interpretation = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'interpretation')->first();
			$current_field_property1 = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property1')->first();
			$current_field_property2 = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property2')->first();

			//get draft content
			$draft_regulation_row = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'regulation')->first();
			$draft_interpretation_row = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'interpretation')->first();
			$draft_regulation_column = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'regulation')->first();
			$draft_interpretation_column = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'interpretation')->first();
			$draft_technical = DraftTechnical::where('changerequest_id', $ChangeRequest->id)->orderBy('content', 'asc')->get();
			$draft_field_regulation = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'regulation')->first();
			$draft_field_interpretation = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'interpretation')->first();
			$draft_field_property1 = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property1')->first();
			$draft_field_property2 = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property2')->first();
		}
		
		if ($ChangeRequest->status == 'approved') {
			//get current content
			$current_regulation_row = HistoryRequirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'regulation')->where('change_type', 'existing')->first();
			$current_interpretation_row = HistoryRequirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'interpretation')->where('change_type', 'existing')->first();
			$current_regulation_column = HistoryRequirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'regulation')->where('change_type', 'existing')->first();
			$current_interpretation_column = HistoryRequirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'interpretation')->where('change_type', 'existing')->first();
			$current_technical = HistoryTechnical::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->orderBy('content', 'asc')->where('change_type', 'existing')->get();
			$current_field_regulation = HistoryRequirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'regulation')->where('change_type', 'existing')->first();
			$current_field_interpretation = HistoryRequirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'interpretation')->where('change_type', 'existing')->first();
			$current_field_property1 = HistoryRequirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property1')->where('change_type', 'existing')->first();
			$current_field_property2 = HistoryRequirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property2')->where('change_type', 'existing')->first();
			
			//get draft content
			$draft_regulation_row = HistoryRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'regulation')->where('change_type', 'new')->first();
			$draft_interpretation_row = HistoryRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'interpretation')->where('change_type', 'new')->first();
			$draft_regulation_column = HistoryRequirement::where('changerequest_id', $ChangeRequest->id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'regulation')->where('change_type', 'new')->first();
			$draft_interpretation_column = HistoryRequirement::where('changerequest_id', $ChangeRequest->id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'interpretation')->where('change_type', 'new')->first();
			$draft_technical = HistoryTechnical::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->orderBy('content', 'asc')->where('change_type', 'new')->get();
			$draft_field_regulation = HistoryRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'regulation')->where('change_type', 'new')->first();
			$draft_field_interpretation = HistoryRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'interpretation')->where('change_type', 'new')->first();
			$draft_field_property1 = HistoryRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property1')->where('change_type', 'new')->first();
			$draft_field_property2 = HistoryRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property2')->where('change_type', 'new')->first();
		}
		
		//perform comparison
		$ChangeRequest->regulation_row = $this->compare_word($current_regulation_row['content'], $draft_regulation_row['content']);
		$ChangeRequest->interpretation_row = $this->compare_word($current_interpretation_row['content'], $draft_interpretation_row['content']);
		$ChangeRequest->regulation_column = $this->compare_word($current_regulation_column['content'], $draft_regulation_column['content']);
		$ChangeRequest->interpretation_column = $this->compare_word($current_interpretation_column['content'], $draft_interpretation_column['content']);
		$ChangeRequest->field_regulation = $this->compare($current_field_regulation['content'],$draft_field_regulation['content']);
		$ChangeRequest->field_interpretation = $this->compare($current_field_interpretation['content'],$draft_field_interpretation['content']);
		$ChangeRequest->field_property1 = $this->compare($current_field_property1['content'],$draft_field_property1['content']);
		$ChangeRequest->field_property2 = $this->compare($current_field_property2['content'],$draft_field_property2['content']);

		$current_technical_string = "";
		if (!empty($current_technical)) {
			foreach ($current_technical as $current_technical_row) {
				if (!is_object($current_technical_row->source)) {
					abort(403, 'The source name no longer exists in the database. Unable to view change request.');
				}

				if (!is_object($current_technical_row->type)) {
					abort(403, 'The type name no longer exists in the database. Unable to view change request.');
				}
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
		
		//one of the two technical should not be empty
		if (!empty($draft_technical) || !empty($current_technical)) {
			$ChangeRequest->technical = $this->compare($current_technical_string,$draft_technical_string);
		}

		return view('changerequests.edit', [
			'changerequest' => $ChangeRequest,
			'template' => Template::find($ChangeRequest->template_id),
			'template_row' => TemplateRow::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->firstOrFail(),
			'template_column' => TemplateColumn::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->firstOrFail(),
			'allowedToChange' => $allowedToChange
		]);
	}
	
	public function process(ChangeRequest $ChangeRequest) {
		//get draft content
		$DraftRegulation_row = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'regulation')->first();
		$DraftInterpretation_row = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'interpretation')->first();
		$DraftRegulation_column = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'regulation')->first();
		$DraftInterpretation_column = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'interpretation')->first();

		$DraftField_property1 = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property1')->first();
		$DraftField_property2 = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property2')->first();
		$DraftField_regulation = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'regulation')->first();
		$DraftField_interpretation = DraftRequirement::where('changerequest_id', $ChangeRequest->id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'interpretation')->first();

		$DraftTechnical = DraftTechnical::where('changerequest_id', $ChangeRequest->id)->get();

		if (!empty($ChangeRequest['template_id'])) {

			//get existing content
			$Regulation_row = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'regulation')->first();
			$Interpretation_row = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'interpretation')->first();
			$Regulation_column = Requirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'regulation')->first();
			$Interpretation_column = Requirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'interpretation')->first();

			$field_property1 = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property1')->first();
			$field_property2 = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property2')->first();
			$field_regulation = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'regulation')->first();
			$field_interpretation = Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'interpretation')->first();
			
			$technical = Technical::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->get();

			//delete any existing if empty is proposed
			if (count($DraftRegulation_row) == 0) {
				Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'regulation')->delete();
			} else {
				//insert
				if (count($Regulation_row) == 0) {
					$Requirements = new Requirement;
					$Requirements->template_id = $ChangeRequest->template_id;
					$Requirements->row_code = $ChangeRequest->row_code;
					$Requirements->content_type = 'regulation';
					$Requirements->content = $DraftRegulation_row->content;
					$Requirements->created_by = $ChangeRequest->creator_id;
					$Requirements->save();
				//update
				} else {
					Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'regulation')->update(['content' => $DraftRegulation_row->content, 'created_by' => $ChangeRequest->creator_id]);

					//submit existing content to archive table
					$HistoryRequirement = new HistoryRequirement;
					$HistoryRequirement->changerequest_id = $ChangeRequest->id;
					$HistoryRequirement->template_id = $ChangeRequest->template_id;
					$HistoryRequirement->row_code = $ChangeRequest->row_code;
					$HistoryRequirement->column_code = null;
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
				$HistoryRequirement->column_code = null;
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
				Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'interpretation')->delete();
			} else {
				//insert
				if (count($Interpretation_row) == 0) {
					$Requirements = new Requirement;
					$Requirements->template_id = $ChangeRequest->template_id;
					$Requirements->row_code = $ChangeRequest->row_code;
					$Requirements->content_type = 'interpretation';
					$Requirements->content = $DraftInterpretation_row->content;
					$Requirements->created_by = $ChangeRequest->creator_id;
					$Requirements->save();
				//update
				} else {
					Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', null)->where('content_type', 'interpretation')->update(['content' => $DraftInterpretation_row->content, 'created_by' => $ChangeRequest->creator_id]);

					//submit existing content to archive table
					$HistoryRequirement = new HistoryRequirement;
					$HistoryRequirement->changerequest_id = $ChangeRequest->id;
					$HistoryRequirement->template_id = $ChangeRequest->template_id;
					$HistoryRequirement->row_code = $ChangeRequest->row_code;
					$HistoryRequirement->column_code = null;
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
				$HistoryRequirement->column_code = null;
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
				Requirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'regulation')->delete();
			} else {
				//insert
				if (count($Regulation_column) == 0) {
					$Requirements = new Requirement;
					$Requirements->template_id = $ChangeRequest->template_id;
					$Requirements->column_code = $ChangeRequest->column_code;
					$Requirements->content_type = 'regulation';
					$Requirements->content = $DraftRegulation_column->content;
					$Requirements->created_by = $ChangeRequest->creator_id;
					$Requirements->save();
				//update
				} else {
					Requirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'regulation')->update(['content' => $DraftRegulation_column->content, 'created_by' => $ChangeRequest->creator_id]);

					//submit existing content to archive table
					$HistoryRequirement = new HistoryRequirement;
					$HistoryRequirement->changerequest_id = $ChangeRequest->id;
					$HistoryRequirement->template_id = $ChangeRequest->template_id;
					$HistoryRequirement->row_code = null;
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
				$HistoryRequirement->row_code = null;
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
				Requirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'interpretation')->delete();
			} else {
				//insert
				if (count($Interpretation_column) == 0) {
					$Requirements = new Requirement;
					$Requirements->template_id = $ChangeRequest->template_id;
					$Requirements->column_code = $ChangeRequest->column_code;
					$Requirements->content_type = 'interpretation';
					$Requirements->content = $DraftInterpretation_column->content;
					$Requirements->created_by = $ChangeRequest->creator_id;
					$Requirements->save();
				//update
				} else {
					Requirement::where('template_id', $ChangeRequest->template_id)->where('column_code', $ChangeRequest->column_code)->where('row_code', null)->where('content_type', 'interpretation')->update(['content' => $DraftInterpretation_column->content, 'created_by' => $ChangeRequest->creator_id]);

					//submit existing content to archive table
					$HistoryRequirement = new HistoryRequirement;
					$HistoryRequirement->changerequest_id = $ChangeRequest->id;
					$HistoryRequirement->template_id = $ChangeRequest->template_id;
					$HistoryRequirement->row_code = null;
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
				$HistoryRequirement->row_code = null;
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
				Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property1')->delete();
			} else {
				//insert
				if (count($field_property1) == 0) {
					$Requirement = new Requirement;
					$Requirement->template_id = $ChangeRequest->template_id;
					$Requirement->row_code = $ChangeRequest->row_code;
					$Requirement->column_code = $ChangeRequest->column_code;
					$Requirement->content_type = 'property1';
					$Requirement->content = $DraftField_property1->content;
					$Requirement->created_by = $ChangeRequest->creator_id;
					$Requirement->save();
				//update
				} else {
					Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property1')->update(['content' => $DraftField_property1->content, 'created_by' => $ChangeRequest->creator_id]);

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
				Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property2')->delete();
			} else {
				//insert
				if (count($field_property2) == 0) {
					$Requirement = new Requirement;
					$Requirement->template_id = $ChangeRequest->template_id;
					$Requirement->row_code = $ChangeRequest->row_code;
					$Requirement->column_code = $ChangeRequest->column_code;
					$Requirement->content_type = 'property2';
					$Requirement->content = $DraftField_property2->content;
					$Requirement->created_by = $ChangeRequest->creator_id;
					$Requirement->save();
				//update
				} else {
					Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'property2')->update(['content' => $DraftField_property2->content, 'created_by' => $ChangeRequest->creator_id]);

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
				Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'regulation')->delete();
			} else {
				//insert
				if (count($field_regulation) == 0) {
					$Requirement = new Requirement;
					$Requirement->template_id = $ChangeRequest->template_id;
					$Requirement->row_code = $ChangeRequest->row_code;
					$Requirement->column_code = $ChangeRequest->column_code;
					$Requirement->content_type = 'regulation';
					$Requirement->content = $DraftField_regulation->content;
					$Requirement->created_by = $ChangeRequest->creator_id;
					$Requirement->save();
				//update
				} else {
					Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'regulation')->update(['content' => $DraftField_regulation->content, 'created_by' => $ChangeRequest->creator_id]);

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
				Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'interpretation')->delete();
			} else {
				//insert
				if (count($field_interpretation) == 0) {
					$Requirement = new Requirement;
					$Requirement->template_id = $ChangeRequest->template_id;
					$Requirement->row_code = $ChangeRequest->row_code;
					$Requirement->column_code = $ChangeRequest->column_code;
					$Requirement->content_type = 'interpretation';
					$Requirement->content = $DraftField_interpretation->content;
					$Requirement->created_by = Auth::user()->id;
					$Requirement->save();
				//update
				} else {
					Requirement::where('template_id', $ChangeRequest->template_id)->where('row_code', $ChangeRequest->row_code)->where('column_code', $ChangeRequest->column_code)->where('content_type', 'interpretation')->update(['content' => $DraftField_interpretation->content, 'created_by' => $ChangeRequest->creator_id]);

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
					$Technical->created_by = $ChangeRequest->creator_id;
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

				$ChangeRequest = ChangeRequest::findOrFail($request->input('changerequest_id'));

				//abort if changerequest is already processed
				if ($ChangeRequest['change_type'] == 'approved') {
					abort(403, 'Error: change request already processed!');
				}
				
				//reopen changerequest
				if ($request->input('change_type') == 'reopen') {
					$ChangeRequest->status = "pending";
					$ChangeRequest->comment = $request->input('comment');
					$ChangeRequest->save();
				}

				//reject changerequest
				if ($request->input('change_type') == "rejected") {

					$ChangeRequest->status = "rejected";
					$ChangeRequest->comment = $request->input('comment');
					$ChangeRequest->save();
					
					//log Event
					Event::fire(new ChangeRequestRejected($ChangeRequest));
				}

				if ($request->input('change_type') == "approved") {

					//update change request
					$ChangeRequest->status = "approved";
					$ChangeRequest->comment = $request->input('comment');
					$ChangeRequest->save();
					
					//process changerequest
					$this->process($ChangeRequest);
					
					//log Event
					Event::fire(new ChangeRequestApproved($ChangeRequest));
				}
			}
		}
		return Redirect::route('changerequests.index')->with('message', 'Changerequest updated.');
	}

	public function destroy(ChangeRequest $ChangeRequest)
	{
		//log Event
		Event::fire(new ChangeRequestDeleted($ChangeRequest));

		$ChangeRequest->delete();
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

			$ChangeRequest = new ChangeRequest;
			$ChangeRequest->template_id = $request->input('template_id');
			$ChangeRequest->row_code = $request->input('row_code');
			$ChangeRequest->column_code = $request->input('column_code');
			$ChangeRequest->creator_id = Auth::user()->id;
			$ChangeRequest->status = 'pending';
			$ChangeRequest->save();

			if ($request->has('regulation_row')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $ChangeRequest->id;
				$draftrequirement->template_id = $request->input('template_id');
				$draftrequirement->row_code = $request->input('row_code');
				$draftrequirement->column_code = null;
				$draftrequirement->content_type = 'regulation';
				$draftrequirement->content = $request->input('regulation_row');
				$draftrequirement->save();
			}

			if ($request->has('interpretation_row')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $ChangeRequest->id;
				$draftrequirement->template_id = $request->input('template_id');
				$draftrequirement->row_code = $request->input('row_code');
				$draftrequirement->column_code = null;
				$draftrequirement->content_type = 'interpretation';
				$draftrequirement->content = $request->input('interpretation_row');
				$draftrequirement->save();
			}

			if ($request->has('regulation_column')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $ChangeRequest->id;
				$draftrequirement->template_id = $request->input('template_id');
				$draftrequirement->row_code = null;
				$draftrequirement->column_code = $request->input('column_code');
				$draftrequirement->content_type = 'regulation';
				$draftrequirement->content = $request->input('regulation_column');
				$draftrequirement->save();
			}

			if ($request->has('interpretation_column')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $ChangeRequest->id;
				$draftrequirement->template_id = $request->input('template_id');
				$draftrequirement->row_code = null;
				$draftrequirement->column_code = $request->input('column_code');
				$draftrequirement->content_type = 'interpretation';
				$draftrequirement->content = $request->input('interpretation_column');
				$draftrequirement->save();
			}

			$technicalData = $request->input('technical');

			foreach($technicalData as $technical) {

				if(isset($technical['action'])) { $row_action = $technical['action']; } else { $row_action = NULL; }
				if(isset($technical['hidden'])) { $row_hidden = $technical['hidden']; } else { $row_hidden = "no"; }
				if(isset($technical['source_id'])) { $row_system = $technical['source_id']; } else { $row_system = 0; }

				if ($row_action != "delete" && $row_system != 0 && $row_hidden == "no" && !(empty($technical['content']))) {
					$drafttechnical = new DraftTechnical;
					$drafttechnical->changerequest_id = $ChangeRequest->id;
					$drafttechnical->source_id = $technical['source_id'];
					$drafttechnical->type_id = $technical['type_id'];
					$drafttechnical->content = $technical['content'];
					$drafttechnical->description = $technical['description'];
					$drafttechnical->save();
				}
			}

			if ($request->has('field_property1')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $ChangeRequest->id;
				$draftrequirement->template_id = $request->input('template_id');
				$draftrequirement->row_code = $request->input('row_code');
				$draftrequirement->column_code = $request->input('column_code');
				$draftrequirement->content_type = 'property1';
				$draftrequirement->content = $request->input('field_property1');
				$draftrequirement->save();
			}

			if ($request->has('field_property2')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $ChangeRequest->id;
				$draftrequirement->template_id = $request->input('template_id');
				$draftrequirement->row_code = $request->input('row_code');
				$draftrequirement->column_code = $request->input('column_code');
				$draftrequirement->content_type = 'property2';
				$draftrequirement->content = $request->input('field_property2');
				$draftrequirement->save();
			}

			if ($request->has('field_interpretation')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $ChangeRequest->id;
				$draftrequirement->template_id = $request->input('template_id');
				$draftrequirement->row_code = $request->input('row_code');
				$draftrequirement->column_code = $request->input('column_code');
				$draftrequirement->content_type = 'interpretation';
				$draftrequirement->content = $request->input('field_interpretation');
				$draftrequirement->save();
			}

			if ($request->has('field_regulation')) {
				$draftrequirement = new DraftRequirement;
				$draftrequirement->changerequest_id = $ChangeRequest->id;
				$draftrequirement->template_id = $request->input('template_id');
				$draftrequirement->row_code = $request->input('row_code');
				$draftrequirement->column_code = $request->input('column_code');
				$draftrequirement->content_type = 'regulation';
				$draftrequirement->content = $request->input('field_regulation');
				$draftrequirement->save();
			}
			
			//if approver field is set, extend object with approver, see listener LogWhenChangeRequestCreated
			if ($request->has('approver')) {
				$ChangeRequest->approver = $request->input('approver');
			}
			
			if (Helper::setting('superadmin_process_directly') == "yes" && Auth::user()->role == "superadmin") {
				
				//update change request
				$ChangeRequest->status = "approved";
				$ChangeRequest->comment = "Changerequest directly approved without review";
				$ChangeRequest->save();

				//process changerequest
				$this->process($ChangeRequest);

				//log Event
				Event::fire(new ChangeRequestApproved($ChangeRequest));
				
				//redirect back to template page
				return Redirect::route('sections.templates.show', [$request->input('section_id'), $request->input('template_id')])->with('message', 'Content directly updated without review approval.');	
				
			} else {
				
				//log Event
				Event::fire(new ChangeRequestCreated($ChangeRequest));
				
				//redirect back to template page
				return Redirect::route('sections.templates.show', [$request->input('section_id'), $request->input('template_id')])->with('message', '<a style="color:white;" href="' . url('/') . '/changerequests/' . $ChangeRequest->id . '/edit">Change request</a> submitted for review.');				
			}
		}
	}

}

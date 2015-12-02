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
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Session;

class SearchController extends Controller
{

	public function search(Request $request) 
	{
		if ($request->isMethod('post')) {
			
			if ($request->has('search')) {

				return view('search.index', [
					'rows' => TemplateRow::where('row_description', 'like', '%' . $request->input('search') . '%')->get(),
					'columns' => TemplateColumn::where('column_description', 'like', '%' . $request->input('search') . '%')->get(),
					'requirements' => Requirement::where('content', 'like', '%' . $request->input('search') . '%')->get(),
					'technicals' => Technical::where('content', 'like', '%' . $request->input('search') . '%')->orWhere('description', 'like', '%' . $request->input('search') . '%')->get(),
					'fields' => TemplateField::where('content', 'like', '%' . $request->input('search') . '%')->where('property', '<>', 'disabled')->get()
				]);
			}
		}
	}

}

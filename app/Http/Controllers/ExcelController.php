<?php

namespace App\Http\Controllers;
use App\ChangeRequest;
use App\DraftField;
use App\DraftRequirement;
use App\DraftTechnical;
use App\Events\TemplateCreated;
use App\HistoryRequirement;
use App\HistoryTechnical;
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
use Maatwebsite\Excel\Facades\Excel;
use Redirect;
use Session;
use Validator;


class ExcelController extends Controller
{
	//function to retrieve section rights based on user id
	public function sectionRights($id) {

		$userrights = UserRights::where('username_id', $id)->get();

		$sectionRights = array();
		$userrights = $userrights->toArray();
		if (!empty($userrights)) {
			foreach ($userrights as $userright) {
				array_push($sectionRights,$userright['section_id']);
			}
		}
		return $sectionRights;
	}

	public function uploadform()
	{
		if (Auth::user()->role == "contributor" || Auth::user()->role == "reviewer" || Auth::user()->role == "guest") {
			abort(403, 'Unauthorized action. You don\'t have access to this template or section');
		}

		//admin and builder are only permitted to upload to own sections
		if (Auth::user()->role == "admin" || Auth::user()->role == "builder") {
			$sectionList = $this->sectionRights(Auth::user()->id);
			$sections = Section::whereIn('id', $sectionList)->orderBy('section_name', 'asc')->get();
			if (empty($sections)) {
				abort(403, 'Unauthorized action. You don\'t have access to any sections');
			}
		}

		//only superadmin can see all sections
		if (Auth::user()->role == "superadmin") {
			$sections = Section::orderBy('section_name', 'asc')->get();
		}

		return view('excel.upload', compact('sections'));
	}

	public function getNameFromNumber($num)
	{
		$numeric = ($num - 1) % 26;
		$letter = chr(65 + $numeric);
		$num2 = intval(($num - 1) / 26);
		if ($num2 > 0) {
			return getNameFromNumber($num2) . $letter;
		} else {
			return $letter;
		}
	}

	public function getExcelColumnNumber($num)
	{
		$numeric = ($num - 1) % 26;
		$letter = chr(65 + $numeric);
		$num2 = intval(($num - 1) / 26);
		if ($num2 > 0) {
			return $this->getNameFromNumber($num2) . $letter;
		} else {
			return $letter;
		}
	}

	public function uploadexcel(Request $request)
	{
		//validate input form
		$this->validate($request, [
			'excel' => 'required|mimes:xls,xlsx',
			'template_name' => 'required|min:4',
			'section_id' => 'required|numeric',
			'template_description' => 'required|min:4'
		]);

		if ($request->file('excel')->isValid()) {

			$validation = Excel::load($request->file('excel'), function ($reader) use ($request, &$templatestructure, &$errors) {

				// Getting all sheets
				$sheets = $reader->get();
				
				//create empty arrays for build structure and for validation
				$templatestructure = array();
				$templatecolumns = array();
				$templaterows = array();
				$errors = array();				

				foreach($sheets as $sheet)
				{
					$worksheetTitle = $sheet->getTitle();
					$arraySheet = $sheet->toArray();
					
					//get column and row count from imported excel
					$highestRow = count($arraySheet) + 1;
					$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
					$highestColumnIndex = count($arraySheet[0]) + 1;

					//start counting unique id content in templatestructure array
					$i = 1;

					if ($worksheetTitle == "structure") {

						//validate if the excel sheets has more than 3 rows
						if ($highestRow > 2) {

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($column = 1; $column < $highestColumnIndex; ++ $column) {

									//4th column is where column naming starts, 3th row is where the row content starts
									$columnid = $column-3;
									$rowid = $row-2;

									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($column);
									$val = $reader->getExcel()->getSheet()->getCell($columnLetter . $row)->getValue();

									//1th row is where the column names are stored, , 4th column is where column numbering starts
									if ($row == 1 && $column > 4) {
										//validate if column_description is not empty
										if (empty($val)) {
											$templatestructure['columns'][$columnid]['column_description'] = $val;
											$templatestructure['columns'][$columnid]['error'] = "1";
											array_push($errors, "empty column_description in template structure");
										} else {
											$templatestructure['columns'][$columnid]['column_description'] = $val;
										}
									}

									//2nd row is where the column numbers are stored, 4th column is where column numbering starts
									if ($row == 2 && $column > 4) {
										//validate if column code is not empty
										if (empty($val)) {
											$templatestructure['columns'][$columnid]['column_code'] = $val;
											$templatestructure['columns'][$columnid]['error'] = "1";
											array_push($errors, "empty column_code in template structure");
										} else {
											$templatestructure['columns'][$columnid]['column_code'] = $val;
											//push to array for validation
											array_push($templatecolumns, $val);
										}
									}

									//more than 2 rows and 1st column is where the row number is stored
									if ($row > 2 && $column == 1) {
										//validate if row code is not empty
										if (empty($val)) {
											$templatestructure['rows'][$rowid]['row_code'] = $val;
											$templatestructure['rows'][$rowid]['error'] = "1";
											array_push($errors, "empty row_code in template structure");
										} else {
											$templatestructure['rows'][$rowid]['row_code'] = $val;
											//push to array for validation
											array_push($templaterows, $val);
										}
									}

									//more than 2 rows and 2nd column is where the row description is stored
									if ($row > 2 && $column == 2) {
										//validate if row description is not empty
										if (empty($val)) {
											$templatestructure['rows'][$rowid]['row_description'] = $val;
											$templatestructure['rows'][$rowid]['error'] = "1";
											array_push($errors, "empty row_description in template structure");
										} else {
											$templatestructure['rows'][$rowid]['row_description'] = $val;
										}
									}

									//more than 2 rows and 3th column is where the row style is stored
									if ($row > 2 && $column == 3) {
										$templatestructure['rows'][$rowid]['row_style'] = $val;
									}

									//more than 2 rows and 4th column is where the row reference is stored
									if ($row > 2 && $column == 4) {
										$templatestructure['rows'][$rowid]['row_reference'] = $val;
									}

									//more than 2 rows and 2 columns is where the disabled cells might be stored
									if ($row > 2 && $column > 4) {

										//todo update with cell color
										$cellcolor = '';

										//set cell column num and row num based on templatestructure
										$cell_column_code = $templatestructure['columns'][$columnid]['column_code'];
										$cell_row_code = $templatestructure['rows'][$rowid]['row_code'];
										//check if cell color is disabled: = D3D3D3
										if ($cellcolor == 'D3D3D3' || $val == 'disabled') {
											$templatestructure['disabledcells'][$i]['column_code'] = $cell_column_code;
											$templatestructure['disabledcells'][$i]['row_code'] = $cell_row_code;
											$i++;
										}
									}
								}
							}
						} else {
							array_push($errors, "incorrect template structure");
						}
					}

					//validatie work sheet with the name column_content
					if ($worksheetTitle == "column_content") {

						if ($highestRow > 1) {

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($column = 1; $column < $highestColumnIndex; ++ $column) {

									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($column);
									$val = $reader->getExcel()->getSheet(1)->getCell($columnLetter . $row)->getValue();

									//1th row is the heading
									if ($row == 1 && ($column == 1 && $val != 'number' || $column == 2 && $val != 'content_type' || $column == 3 && $val != 'content')) {
										//validate if heading is correct
										array_push($errors, "incorrect heading on column content sheet");
									}
									//add content to template structure column_content
									if ($row > 1 && $column == 1) {
										//validate if column_code exists in template columns
										if (!(in_array($val, $templatecolumns, true))) {
											$templatestructure['column_content'][$i]['error'] = "1";
											array_push($errors, "column_code cannot be found in template structure");
										}
										$templatestructure['column_content'][$i]['column_code'] = $val;
									}
									if ($row > 1 && $column == 2) {
										//validate if content_type contains regulation or interpretation
										if (!($val == 'regulation' || $val == 'interpretation' || $val == 'reference')) {
											$templatestructure['column_content'][$i]['error'] = "1";
											array_push($errors, "content_type not a valid value");
										}
										$templatestructure['column_content'][$i]['content_type'] = $val;
									}
									if ($row > 1 && $column == 3) {
										$templatestructure['column_content'][$i]['content'] = $val;
									}
								}
								$i++;
							}
						}
					}

					//validatie work sheet with the name row_content
					if ($worksheetTitle == "row_content") {

						if ($highestRow > 1) {

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($column = 1; $column < $highestColumnIndex; ++ $column) {

									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($column);
									$val = $reader->getExcel()->getSheet(2)->getCell($columnLetter . $row)->getValue();

									//1th row is the heading
									if ($row == 1 && ($column == 1 && $val != 'number' || $column == 2 && $val != 'content_type' || $column == 3 && $val != 'content')) {
										array_push($errors, "incorrect heading on row content sheet");
									}
									if ($row > 1 && $column == 1) {
										if (!(in_array($val, $templaterows, true))) {
											$templatestructure['row_content'][$i]['error'] = "1";
											array_push($errors, "row_code cannot be found in template structure");
										}
										$templatestructure['row_content'][$i]['row_code'] = $val;
									}
									if ($row > 1 && $column == 2) {
										//validate if content_type contains regulation or interpretation
										if (!($val == 'regulation' || $val == 'interpretation' || $val == 'reference')) {
											$templatestructure['row_content'][$i]['error'] = "1";
											array_push($errors, "content_type not a valid value");
										}
										$templatestructure['row_content'][$i]['content_type'] = $val;
									}
									if ($row > 1 && $column == 3) {
										$templatestructure['row_content'][$i]['content'] = $val;
									}
								}
								$i++;
							}
						}
					}

					//validatie work sheet with the name column_content
					if ($worksheetTitle == "template_content") {

						for ($row = 1; $row <= $highestRow; ++ $row) {

							for ($column = 1; $column < $highestColumnIndex; ++ $column) {

								//set column letter and retrieve value
								$columnLetter = $this->getExcelColumnNumber($column);
								$val = $reader->getExcel()->getSheet(5)->getCell($columnLetter . $row)->getValue();

								//1th row is the heading
								if ($row == 1 && $column < 3) {
									if (empty($val)) {
										array_push($errors, "empty header on the template_content sheet");
									}
								}
								//next rows contain content
								//check content type
								if ($row == 2 && $column == 1) {
									if ($column == 1 && $val != 'template_longdesc') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "template_content is not correctly set");
									}
								}
								//add content to array
								if ($row == 2 && $column == 2) {
									$templatestructure['template_content']['template_longdesc'] = $val;
								}
								//check content type
								if ($row == 3 && $column == 1) {
									if ($column == 1 && $val != 'frequency_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "frequency_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 3 && $column == 2) {
									$templatestructure['template_content']['frequency_description'] = $val;
								}
								//check content type
								if ($row == 4 && $column == 1) {
									if ($column == 1 && $val != 'reporting_dates_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "reporting_dates_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 4 && $column == 2) {
									$templatestructure['template_content']['reporting_dates_description'] = $val;
								}
								//check content type
								if ($row == 5 && $column == 1) {
									if ($column == 1 && $val != 'main_changes_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "main_changes_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 5 && $column == 2) {
									$templatestructure['template_content']['main_changes_description'] = $val;
								}
								//check content type
								if ($row == 6 && $column == 1) {
									if ($column == 1 && $val != 'links_other_temp_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "links_other_temp_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 6 && $column == 2) {
									$templatestructure['template_content']['links_other_temp_description'] = $val;
								}
								//check content type
								if ($row == 7 && $column == 1) {
									if ($column == 1 && $val != 'process_and_organisation_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "process_and_organisation_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 7 && $column == 2) {
									$templatestructure['template_content']['process_and_organisation_description'] = $val;
								}
							}
						}
					}

					if ($worksheetTitle == "sourcing") {

						$type_results = TechnicalType::select('id', 'type_name')->get();

						//create empty array to lookup types
						$type_array = array();

						//restructure array from database results
						if (!empty($type_results)) {
							foreach($type_results as $type_result) {
								$type_id = $type_result['id'];
								$type_array[$type_id] = $type_result['type_name'];
							}
						}

						$source_results = TechnicalSource::select('id', 'source_name')->get();

						//create empty array to lookup sources
						$source_array = array();

						//restructure array from database results
						if (!empty($source_results)) {
							foreach($source_results as $source_result) {
								$source_id = $source_result['id'];
								$source_array[$source_id] = $source_result['source_name'];
							}
						}

						if ($highestRow > 1) {

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($column = 1; $column < $highestColumnIndex; ++ $column) {

									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($column);
									$val = $reader->getExcel()->getSheet(4)->getCell($columnLetter . $row)->getValue();

									//1th row is the heading
									if ($row == 1) {
										if ($column == 1 && $val != 'column_code' || $column == 2 && $val != 'row_code' || $column == 3 && $val != 'type' || $column == 4 && $val != 'source' || $column == 5 && $val != 'value' || $column == 6 && $val != 'description') {
											array_push($errors, "incorrect heading on sourcing sheet");
										}
									}
									if ($row > 1 && $column == 1) {
										//validate if column number exists in template columns
										if (!(in_array($val, $templatecolumns, true))) {
											$templatestructure['sourcing'][$i]['error'] = "1";
											array_push($errors, "column_code cannot be found in template structure");
										}
										$templatestructure['sourcing'][$i]['column_code'] = $val;
									}
									if ($row > 1 && $column == 2) {
										//validate if column number exists in template rows
										if (!(in_array($val, $templaterows, true))) {
											$templatestructure['sourcing'][$i]['error'] = "1";
											array_push($errors, "row_code cannot be found in template structure");
										}
										$templatestructure['sourcing'][$i]['row_code'] = $val;
									}
									if ($row > 1 && $column == 3) {
										if (!(in_array($val, $type_array, true))) {
											$templatestructure['sourcing'][$i]['error'] = "1";
											array_push($errors, "type_name is not a valid value");
										}
										$key = array_search($val, $type_array);
										$templatestructure['sourcing'][$i]['type'] = $key;
									}
									if ($row > 1 && $column == 4) {
										if (!(in_array($val, $source_array, true))) {
											$templatestructure['sourcing'][$i]['error'] = "1";
											array_push($errors, "source_name is not a valid value");
										}
										$key = array_search($val, $source_array);
										$templatestructure['sourcing'][$i]['source'] = $key;
									}
									if ($row > 1 && $column == 5) {
										$templatestructure['sourcing'][$i]['value'] = $val;
									}
									if ($row > 1 && $column == 6) {
										$templatestructure['sourcing'][$i]['description'] = $val;
									}
								}
								$i++;
							}
						}
					}

					if ($worksheetTitle == "field_content") {

						if ($highestRow > 1) {

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($column = 1; $column < $highestColumnIndex; ++ $column) {

									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($column);
									$val = $reader->getExcel()->getSheet(3)->getCell($columnLetter . $row)->getValue();

									//1th row is the heading
									if ($row == 1 && ($column == 1 && $val != 'column_code' || $column == 2 && $val != 'row_code' || $column == 3 && $val != 'content_type' || $column == 4 && $val != 'content')) {
										array_push($errors, "incorrect heading on field content sheet");
									}
									if ($row > 1 && $column == 1) {
										if (!(in_array($val, $templatecolumns, true))) {
											$templatestructure['field_content'][$i]['error'] = "1";
											array_push($errors, "column_code cannot be found in template structure");
										}
										$templatestructure['field_content'][$i]['column_code'] = $val;
									}
									if ($row > 1 && $column == 2) {
										if (!(in_array($val, $templaterows, true))) {
											$templatestructure['field_content'][$i]['error'] = "1";
											array_push($errors, "row_code cannot be found in template structure");
										}
										$templatestructure['field_content'][$i]['row_code'] = $val;
									}
									if ($row > 1 && $column == 3) {
										//validate if content_type contains regulation or interpretation
										if (!($val == 'regulation' || $val == 'interpretation' || $val == 'property1' || $val == 'property2')) {
											$templatestructure['field_content'][$i]['error'] = "1";
											array_push($errors, "incorrect content_type used");
										//validate if column number exists in templatecolumns
										}
										$templatestructure['field_content'][$i]['content_type'] = $val;
									}
									if ($row > 1 && $column == 4) {
										$templatestructure['field_content'][$i]['content'] = $val;
									}
								}
								$i++;
							}
						}
					}
				}
			});

			if (!empty($errors)) {
				//Create new arrays to restructure result
				$arraydisabled=array();
				//Restructure array
				if (!empty($templatestructure['disabledcells'])) {
					foreach ($templatestructure['disabledcells'] as $disabledField) {
						$rowname = $disabledField['row_code'];
						$columnname = $disabledField['column_code'];
						$field = 'column' . trim($columnname) . '-' . 'row' . trim($rowname);
						$arraydisabled[$field] = 'disabled';
					}
				}
				return view('errors.excelupload', compact('templatestructure','errors','arraydisabled'));
			} else {

				$template = new Template;
				$template->section_id = $request->input('section_id');
				$template->template_name = $request->input('template_name');
				$template->template_shortdesc = $request->input('template_description');

				//add additional template content
				if (!empty($templatestructure['template_content'])) {
					$template->template_longdesc = $templatestructure['template_content']['template_longdesc'];
					$template->frequency_description = $templatestructure['template_content']['frequency_description'];
					$template->reporting_dates_description = $templatestructure['template_content']['reporting_dates_description'];
					$template->main_changes_description = $templatestructure['template_content']['main_changes_description'];
					$template->links_other_temp_description = $templatestructure['template_content']['links_other_temp_description'];
					$template->process_and_organisation_description = $templatestructure['template_content']['process_and_organisation_description'];
				}

				$template->visible = 'No';
				$template->save();

				if (empty($templatestructure['columns']) || empty($templatestructure['rows'])) {
					abort(403, 'Incorrect Excel template. Excel sheet structure needs a least one column or one row!');
				}

				//add template column to database
				$i = 1;
				foreach($templatestructure['columns'] as $columnline) {
					$column = new TemplateColumn;
					$column->template_id = $template->id;
					$column->column_num = $i;
					$column->column_code = $columnline['column_code'];
					$column->column_description = $columnline['column_description'];
					$column->save();
					$i++;
				}

				//add template rows to database
				$i = 1;
				foreach($templatestructure['rows'] as $rowline) {
					$row = new TemplateRow;
					$row->template_id = $template->id;
					$row->row_num = $i;
					$row->row_code = $rowline['row_code'];
					$row->row_description = $rowline['row_description'];
					$row->row_reference = $rowline['row_reference'];
					$row->save();
					$i++;
				}

				//add template field content to database
				if (!empty($templatestructure['field_content'])) {
					foreach($templatestructure['field_content'] as $field_content) {
						$Requirement = new Requirement;
						$Requirement->template_id = $template->id;
						$Requirement->row_code = $field_content['row_code'];
						$Requirement->column_code = $field_content['column_code'];
						$Requirement->content_type = $field_content['content_type'];
						$Requirement->content = $field_content['content'];
						$Requirement->save();

						//submit new content to archive table
						$HistoryRequirement = new HistoryRequirement;
						$HistoryRequirement->changerequest_id = '0';
						$HistoryRequirement->template_id = $template->id;
						$HistoryRequirement->row_code = $field_content['row_code'];
						$HistoryRequirement->column_code = $field_content['column_code'];
						$HistoryRequirement->content_type = $field_content['content_type'];
						$HistoryRequirement->content = $field_content['content'];
						$HistoryRequirement->change_type = 'excel';
						$HistoryRequirement->created_by = Auth::user()->id;
						$HistoryRequirement->submission_date = null;
						$HistoryRequirement->approved_by = Auth::user()->id;
						$HistoryRequirement->save();

					}
				}

				//add template row content to database
				if (!empty($templatestructure['row_content'])) {
					foreach($templatestructure['row_content'] as $key => $requirement) {
						$templaterequirement = new Requirement;
						$templaterequirement->template_id = $template->id;
						$templaterequirement->row_code = $requirement['row_code'];
						$templaterequirement->content_type = $requirement['content_type'];
						$templaterequirement->content = $requirement['content'];
						$templaterequirement->save();

						//submit new content to archive table
						$HistoryRequirement = new HistoryRequirement;
						$HistoryRequirement->changerequest_id = '0';
						$HistoryRequirement->template_id = $template->id;
						$HistoryRequirement->row_code = $field_content['row_code'];
						$HistoryRequirement->column_code = '';
						$HistoryRequirement->content_type = $field_content['content_type'];
						$HistoryRequirement->content = $field_content['content'];
						$HistoryRequirement->change_type = 'excel';
						$HistoryRequirement->created_by = Auth::user()->id;
						$HistoryRequirement->submission_date = null;
						$HistoryRequirement->approved_by = Auth::user()->id;
						$HistoryRequirement->save();

					}
				}

				//add template column content to database
				if (!empty($templatestructure['column_content'])) {
					foreach($templatestructure['column_content'] as $key => $requirement) {
						$templaterequirement = new Requirement;
						$templaterequirement->template_id = $template->id;
						$templaterequirement->column_code = $requirement['column_code'];
						$templaterequirement->content_type = $requirement['content_type'];
						$templaterequirement->content = $requirement['content'];
						$templaterequirement->save();

						//submit new content to archive table
						$HistoryRequirement = new HistoryRequirement;
						$HistoryRequirement->changerequest_id = '0';
						$HistoryRequirement->template_id = $template->id;
						$HistoryRequirement->row_code = '';
						$HistoryRequirement->column_code = $field_content['column_code'];
						$HistoryRequirement->content_type = $field_content['content_type'];
						$HistoryRequirement->content = $field_content['content'];
						$HistoryRequirement->change_type = 'excel';
						$HistoryRequirement->created_by = Auth::user()->id;
						$HistoryRequirement->submission_date = null;
						$HistoryRequirement->approved_by = Auth::user()->id;
						$HistoryRequirement->save();

					}
				}

				//add disabled cells to database
				if (!empty($templatestructure['disabledcells'])) {
					foreach($templatestructure['disabledcells'] as $disabledcell) {
						$Requirement = new Requirement;
						$Requirement->template_id = $template->id;
						$Requirement->row_code = $disabledcell['row_code'];
						$Requirement->column_code = $disabledcell['column_code'];
						$Requirement->content_type = 'disabled';
						$Requirement->save();
					}
				}

				//add technical content to database
				if (!empty($templatestructure['sourcing'])) {
					foreach($templatestructure['sourcing'] as $sourcing) {
						$technical = new Technical;
						$technical->template_id = $template->id;
						$technical->row_code = $sourcing['row_code'];
						$technical->column_code = $sourcing['column_code'];
						$technical->source_id = $sourcing['source'];
						$technical->type_id = $sourcing['type'];
						$technical->content = $sourcing['value'];
						$technical->description = $sourcing['description'];
						$technical->save();

						//submit new content to archive table
						$HistoryTechnical = new HistoryTechnical;
						$HistoryTechnical->changerequest_id = '0';
						$HistoryTechnical->template_id = $template->id;
						$HistoryTechnical->row_code = $sourcing['row_code'];
						$HistoryTechnical->column_code = $sourcing['column_code'];
						$HistoryTechnical->type_id = $sourcing['type'];
						$HistoryTechnical->source_id = $sourcing['source'];
						$HistoryTechnical->content = $sourcing['value'];
						$HistoryTechnical->description = $sourcing['description'];
						$HistoryTechnical->change_type = 'excel';
						$HistoryTechnical->created_by = Auth::user()->id;
						$HistoryTechnical->submission_date = null;
						$HistoryTechnical->approved_by = Auth::user()->id;
						$HistoryTechnical->save();
					}
				}

				Event::fire(new TemplateCreated($template));

				return Redirect::to('/sections')->with('message', 'New template successfully added to the database.');
			}
		}
	}

	//function to export template to excel
	public function export($id)
	{

		$template = Template::findOrFail($id);

		Excel::create($template->template_name, function($excel) use ($id)  {

			// Our first sheet
			$excel->sheet('structure', function($sheet) use ($id) {

				$template = Template::findOrFail($id);

				$sheet->SetCellValue('A1', 'Row#');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(6);
				$sheet->SetCellValue('B1', 'Row description');
				$sheet->getStyle('B1')->getFont()->setBold(true);
				$sheet->getColumnDimension('B')->setWidth(50);
				$sheet->SetCellValue('C1', 'Style');
				$sheet->getStyle('C1')->getFont()->setBold(true);
				$sheet->getColumnDimension('C')->setWidth(40);
				$sheet->SetCellValue('D1', 'Reference');
				$sheet->getStyle('D1')->getFont()->setBold(true);
				$sheet->getColumnDimension('D')->setWidth(40);

				//starting column letter
				$letter = "D";
				//create empty array to store template structure content, needed to retrieve row and column id for disabled fields
				$templatestructure = array();
				//start counter
				$i = 1;

				//Add remaining columns
				foreach ($template->columns as $column) {
					//add column id and row_code to structure
					$column_code = trim($column['column_code']);
					$templatestructure['columns'][$column_code] = $i;
					$i++;
					$letter++;
					$sheet->SetCellValue($letter . '1', $column['column_description']);
					$sheet->getStyle($letter . '1')->getFont()->setBold(true);
					$sheet->getColumnDimension($letter)->setWidth(20);
					$sheet->setCellValueExplicit($letter . '2', $column['column_code']);
				}

				$sheet->cells('A1:' . $letter . '1', function($cells) {
					$cells->setBackground('#18bc9c');
				});

				$sheet->getRowDimension('1')->setRowHeight(20);

				$sheet->cells('A2:' . $letter . '2', function($cells) {
					$cells->setBackground('#eeeeee');
				});

				$sheet->getRowDimension('2')->setRowHeight(20);

				//starting row number
				$rownumber = "3";

				//start counter
				$i = 1;

				//add rows to template
				foreach ($template->rows as $row) {
					//add row id and row_code to structure
					$row_code = trim($row['row_code']);
					$templatestructure['rows'][$row_code] = $i;
					$i++;
					$sheet->getRowDimension($rownumber)->setRowHeight(20);
					//row_code data as string
					$sheet->setCellValueExplicit('A' . $rownumber, $row['row_code']);
					$sheet->SetCellValue('B' . $rownumber, $row['row_description']);
					$sheet->SetCellValue('C' . $rownumber, $row['row_property']);
					$sheet->SetCellValue('D' . $rownumber, $row['row_reference']);
					$sheet->getStyle('A' . $rownumber . ':D' . $rownumber)->getFill()->getStartColor()->setARGB('FAFAFA');
					$rownumber++;
				}

				//restart column letter and row number
				$letter = "D";
				//set borders on all cells
				foreach ($template->columns as $column) {
					$rownumber = "2";
					$letter++;
					foreach ($template->rows as $row) {
						$rownumber++;
					}
				}

				$disabled = Requirement::where('template_id', $template->id)->where('content_type', 'disabled')->get();

				//set grey fields, add two to put it correctly in the template
				if (!empty($disabled)) {
					foreach($disabled as $disabledrows) {
						//get row_code and column_code from array
						$disabled_row_code = $disabledrows['row_code'];
						$disabled_column_code = $disabledrows['column_code'];
						//get row_code and column name from structure
						$structurerowid = $templatestructure['rows'][$disabled_row_code];
						$structurecolumnid = $templatestructure['columns'][$disabled_column_code];
						//jump in two from top and four from left
						$structurerowid = $structurerowid + 2;
						$structurecolumnid = $structurecolumnid + 4;
						//convert columnid to letter
						//$columnLetter = PHPExcel_Cell::stringFromColumnIndex($structurecolumnid);
						$columnLetter = $this->getExcelColumnNumber($structurecolumnid);
						//grey color
						//add disabled to cell, otherwise the import won't pick it up
						$sheet->setCellValueExplicit($columnLetter . $structurerowid, 'disabled');
						$sheet->getStyle($columnLetter . $structurerowid)->getFill()->getStartColor()->setARGB('D3D3D3');
						$sheet->cells($columnLetter . $structurerowid, function($cells) {
							$cells->setBackground('#d3d3d3');
						});
					}
				}

				// Set border for range
				$sheet->setBorder('A1:' . $letter . $rownumber, 'thin');


			});

			// Our second sheet
			$excel->sheet('column_content', function($sheet) use ($id) {

				//set first column for column_content
				//Column part
				$sheet->SetCellValue('A1', 'number');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(16);

				$sheet->SetCellValue('B1', 'content_type');
				$sheet->getStyle('B1')->getFont()->setBold(true);
				$sheet->getColumnDimension('B')->setWidth(20);

				$sheet->SetCellValue('C1', 'content');
				$sheet->getStyle('C1')->getFont()->setBold(true);
				$sheet->getColumnDimension('C')->setWidth(100);


				$sheet->cells('A1:C1', function($cells) {
					$cells->setBackground('#18bc9c');
				});

				$sheet->getStyle('A1:C1')->getFill()->getStartColor()->setARGB('dff0d8');
				$sheet->getRowDimension('1')->setRowHeight(20);

				$column_content  = Requirement::where('template_id', $id)->where('row_code', '')->orWhere('row_code', null)->where('content', '!=' , '')->orderBy('column_code', 'asc')->get();

				$columncontentcount = 2;
				//add content to excel
				if (!empty($column_content )) {
					foreach($column_content  as $key => $value) {
						$column_code = trim($value['column_code']);
						$sheet->setCellValueExplicit('A' . $columncontentcount, $column_code)
						->setCellValueExplicit('B' . $columncontentcount, $value['content_type'])
						->setCellValueExplicit('C' . $columncontentcount, $value['content']);
						$columncontentcount++;
					}
				}

			});

			// Our third sheet
			$excel->sheet('row_content', function($sheet) use ($id) {

				//set first column for column_content
				//Column part
				$sheet->SetCellValue('A1', 'number');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(16);

				$sheet->SetCellValue('B1', 'content_type');
				$sheet->getStyle('B1')->getFont()->setBold(true);
				$sheet->getColumnDimension('B')->setWidth(20);

				$sheet->SetCellValue('C1', 'content');
				$sheet->getStyle('C1')->getFont()->setBold(true);
				$sheet->getColumnDimension('C')->setWidth(100);


				$sheet->cells('A1:C1', function($cells) {
					$cells->setBackground('#18bc9c');
				});

				$sheet->getStyle('A1:C1')->getFill()->getStartColor()->setARGB('dff0d8');
				$sheet->getRowDimension('1')->setRowHeight(20);

				$row_contents  = Requirement::where('template_id', $id)->where('column_code', '')->orWhere('column_code', null)->where('content', '!=' , '')->orderBy('column_code', 'asc')->get();

				$rowcontentcount = 2;

				//add content to excel
				if (!empty($row_contents)) {
					foreach($row_contents as $key => $value) {
						$row_code = trim($value['row_code']);
						$sheet->setCellValueExplicit('A' . $rowcontentcount, $row_code)
						->setCellValueExplicit('B' . $rowcontentcount, $value['content_type'])
						->setCellValueExplicit('C' . $rowcontentcount, $value['content']);
						$rowcontentcount++;
					}
				}

			});

			// Our fourth sheet
			$excel->sheet('field_content', function($sheet) use ($id) {

				//set first column for field_content
				//Column part
				$sheet->SetCellValue('A1', 'column_code');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(16);

				$sheet->SetCellValue('B1', 'row_code');
				$sheet->getStyle('B1')->getFont()->setBold(true);
				$sheet->getColumnDimension('B')->setWidth(20);

				$sheet->SetCellValue('C1', 'content_type');
				$sheet->getStyle('C1')->getFont()->setBold(true);
				$sheet->getColumnDimension('C')->setWidth(20);

				$sheet->SetCellValue('D1', 'content');
				$sheet->getStyle('D1')->getFont()->setBold(true);
				$sheet->getColumnDimension('D')->setWidth(30);

				$sheet->cells('A1:D1', function($cells) {
					$cells->setBackground('#18bc9c');
				});

				$sheet->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('dff0d8');
				$sheet->getRowDimension('1')->setRowHeight(20);

				$field_contents = Requirement::where('template_id', $id)->where('content_type', '!=' , 'disabled')->whereNotNull('row_code')->where('row_code', '<>', '')->whereNotNull('column_code')->where('column_code', '<>', '')->orderBy('row_code', 'asc')->orderBy('column_code', 'asc')->get();

				$fieldcontentcount = 2;
				//set grey fields, add two to put it correctly in the template
				if (!empty($field_contents)) {
					foreach($field_contents as $key => $value) {
						$sheet->setCellValueExplicit('A' . $fieldcontentcount, $value['column_code'])
						->setCellValueExplicit('B' . $fieldcontentcount, $value['row_code'])
						->setCellValueExplicit('C' . $fieldcontentcount, $value['content_type'])
						->setCellValueExplicit('D' . $fieldcontentcount, $value['content']);
						$fieldcontentcount++;
					}
				}

			});

			// Our firth sheet
			$excel->sheet('sourcing', function($sheet) use ($id) {

				//set first column for field_content
				//Column part
				$sheet->SetCellValue('A1', 'column_code');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(16);

				$sheet->SetCellValue('B1', 'row_code');
				$sheet->getStyle('B1')->getFont()->setBold(true);
				$sheet->getColumnDimension('B')->setWidth(20);

				$sheet->SetCellValue('C1', 'type');
				$sheet->getStyle('C1')->getFont()->setBold(true);
				$sheet->getColumnDimension('C')->setWidth(20);

				$sheet->SetCellValue('D1', 'source');
				$sheet->getStyle('D1')->getFont()->setBold(true);
				$sheet->getColumnDimension('D')->setWidth(30);

				$sheet->SetCellValue('E1', 'value');
				$sheet->getStyle('E1')->getFont()->setBold(true);
				$sheet->getColumnDimension('E')->setWidth(30);

				$sheet->SetCellValue('F1', 'description');
				$sheet->getStyle('F1')->getFont()->setBold(true);
				$sheet->getColumnDimension('F')->setWidth(30);

				$sheet->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('dff0d8');
				$sheet->getRowDimension('1')->setRowHeight(20);

				$sheet->cells('A1:F1', function($cells) {
					$cells->setBackground('#18bc9c');
				});

				$sheet->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('dff0d8');
				$sheet->getRowDimension('1')->setRowHeight(20);

				$field_contents = Technical::where('template_id', $id)->get();

				$fieldcontentcount = 2;
				//set grey fields, add two to put it correctly in the template
				if (!empty($field_contents)) {
					foreach($field_contents as $key => $value) {
						$sheet->setCellValueExplicit('A' . $fieldcontentcount, $value['column_code'])
						->setCellValueExplicit('B' . $fieldcontentcount, $value['row_code'])
						->setCellValueExplicit('C' . $fieldcontentcount, $value->type->type_name)
						->setCellValueExplicit('D' . $fieldcontentcount, $value->source->source_name)
						->setCellValueExplicit('E' . $fieldcontentcount, $value['content'])
						->setCellValueExplicit('F' . $fieldcontentcount, $value['description']);
						$fieldcontentcount++;
					}
				}


			});

			// Our sixth sheet
			$excel->sheet('template_content', function($sheet) use ($id) {

				$template_content = Template::find($id);

				//style
				$sheet->getColumnDimension('A')->setWidth(40);
				$sheet->getColumnDimension('B')->setWidth(80);
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getStyle('B1')->getFont()->setBold(true);

				$sheet->cells('A1:B1', function($cells) {
					$cells->setBackground('#18bc9c');
				});

				//set first column for field_content
				//Column part
				$sheet->SetCellValue('A1', 'content type:')
					->SetCellValue('A2', 'template_longdesc')
					->SetCellValue('A3', 'frequency_description')
					->SetCellValue('A4', 'reporting_dates_description')
					->SetCellValue('A5', 'main_changes_description')
					->SetCellValue('A6', 'links_other_temp_description')
					->SetCellValue('A7', 'process_and_organisation_description')
					->SetCellValue('B1', 'content:')
					->SetCellValue('B2', $template_content['template_longdesc'])
					->SetCellValue('B3', $template_content['frequency_description'])
					->SetCellValue('B4', $template_content['reporting_dates_description'])
					->SetCellValue('B5', $template_content['main_changes_description'])
					->SetCellValue('B6', $template_content['links_other_temp_description'])
					->SetCellValue('B7', $template_content['process_and_organisation_description']);
			});

			// Our seventh sheet
			$excel->sheet('explanation', function($sheet) use ($id) {

				//set first column for field_content
				//Column part
				$sheet->SetCellValue('A1', 'for column and row content:')
				->SetCellValue('A2', 'legal_desc')
				->SetCellValue('A3', 'interpretation_desc')
				->SetCellValue('A4', 'reference')
				->SetCellValue('A6', 'for field_content')
				->SetCellValue('A7', 'property1')
				->SetCellValue('A8', 'property2')
				->SetCellValue('A9', 'legal_desc')
				->SetCellValue('A10', 'interpretation_desc')
				->SetCellValue('A12', 'styles')
				->SetCellValue('A13', 'bold')
				->SetCellValue('A14', 'tab')
				->SetCellValue('A15', 'doubletab')
				->SetCellValue('A16', 'disabled')
				->SetCellValue('B2', 'Legal description, such as IAS or CRD IV content')
				->SetCellValue('B3', 'Own interpretation')
				->SetCellValue('B4', 'Reference to guidance or article number')
				->SetCellValue('B7', 'Reference for a field, such as a internal id or name')
				->SetCellValue('B8', 'Value highlighted in the report')
				->SetCellValue('B9', 'Reporting Business Rule for the specific cell')
				->SetCellValue('B10', 'Standard Operating Procedure for the specific cell');

				$sheet->getColumnDimension('A')->setWidth(35);

				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getStyle('A6')->getFont()->setBold(true);
				$sheet->getStyle('A12')->getFont()->setBold(true);

				$sheet->getStyle('A16')->getFill()->getStartColor()->setARGB('D3D3D3');
				$sheet->getStyle('A1')->getFill()->getStartColor()->setARGB('dff0d8');
				$sheet->getStyle('A6')->getFill()->getStartColor()->setARGB('dff0d8');
			});

		})->download('xlsx');

	}

    public function exportchanges()
    {
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

		Excel::create('ChangeRequests', function($excel)  {

			// Our first sheet
			$excel->sheet('regulatory_content', function($sheet) {

				$sheet->SetCellValue('A1', 'changerequest_id');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(15);

				$sheet->SetCellValue('B1', 'template');
				$sheet->getStyle('B1')->getFont()->setBold(true);
				$sheet->getColumnDimension('B')->setWidth(30);

				$sheet->SetCellValue('C1', 'row_code');
				$sheet->getStyle('C1')->getFont()->setBold(true);
				$sheet->getColumnDimension('C')->setWidth(15);

				$sheet->SetCellValue('D1', 'column_code');
				$sheet->getStyle('D1')->getFont()->setBold(true);
				$sheet->getColumnDimension('D')->setWidth(15);

				$sheet->SetCellValue('E1', 'content_type');
				$sheet->getStyle('E1')->getFont()->setBold(true);
				$sheet->getColumnDimension('E')->setWidth(15);

				$sheet->SetCellValue('F1', 'content');
				$sheet->getStyle('F1')->getFont()->setBold(true);
				$sheet->getColumnDimension('F')->setWidth(15);

				$sheet->SetCellValue('G1', 'change_type');
				$sheet->getStyle('G1')->getFont()->setBold(true);
				$sheet->getColumnDimension('G')->setWidth(15);

				$sheet->SetCellValue('H1', 'created_by');
				$sheet->getStyle('H1')->getFont()->setBold(true);
				$sheet->getColumnDimension('H')->setWidth(15);

				$sheet->SetCellValue('I1', 'submission_date');
				$sheet->getStyle('I1')->getFont()->setBold(true);
				$sheet->getColumnDimension('I')->setWidth(15);

				$sheet->SetCellValue('J1', 'approved_by');
				$sheet->getStyle('J1')->getFont()->setBold(true);
				$sheet->getColumnDimension('J')->setWidth(15);

				$sheet->SetCellValue('K1', 'approval_date');
				$sheet->getStyle('K1')->getFont()->setBold(true);
				$sheet->getColumnDimension('K')->setWidth(15);

				//start counter
				$i = 1;

				$HistoryRequirement = HistoryRequirement::all();

				foreach($HistoryRequirement as $row) {

					$i++;

					$sheet->setCellValueExplicit('A' . $i, $row['changerequest_id'])
						  ->setCellValueExplicit('B' . $i, $row['template_id'])
						  ->setCellValueExplicit('C' . $i, $row['row_code'])
						  ->setCellValueExplicit('D' . $i, $row['column_code'])
						  ->setCellValueExplicit('E' . $i, $row['content_type'])
						  ->setCellValueExplicit('F' . $i, $row['content'])
						  ->setCellValueExplicit('G' . $i, $row['change_type'])
						  ->setCellValueExplicit('I' . $i, $row['submission_date'])
						  ->setCellValueExplicit('K' . $i, $row['created_at']);

					//query for user table
					$created_by = User::where('id', $row['created_by'])->first();
					if (!empty($created_by)) {
						$sheet->setCellValueExplicit('H' . $i, $created_by['username']);
					}

					//query for user table
					$approved_by = User::where('id', $row['approved_by'])->first();
					if (!empty($approved_by)) {
						$sheet->setCellValueExplicit('J' . $i, $approved_by['username']);
					}
				}
			});


			// Our second sheet
			$excel->sheet('technical_content', function($sheet) {

				$sheet->SetCellValue('A1', 'changerequest_id');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(15);

				$sheet->SetCellValue('B1', 'template');
				$sheet->getStyle('B1')->getFont()->setBold(true);
				$sheet->getColumnDimension('B')->setWidth(30);

				$sheet->SetCellValue('C1', 'row_code');
				$sheet->getStyle('C1')->getFont()->setBold(true);
				$sheet->getColumnDimension('C')->setWidth(15);

				$sheet->SetCellValue('D1', 'column_code');
				$sheet->getStyle('D1')->getFont()->setBold(true);
				$sheet->getColumnDimension('D')->setWidth(15);

				$sheet->SetCellValue('E1', 'source_name');
				$sheet->getStyle('E1')->getFont()->setBold(true);
				$sheet->getColumnDimension('E')->setWidth(15);

				$sheet->SetCellValue('F1', 'type_name');
				$sheet->getStyle('F1')->getFont()->setBold(true);
				$sheet->getColumnDimension('F')->setWidth(15);

				$sheet->SetCellValue('G1', 'content');
				$sheet->getStyle('G1')->getFont()->setBold(true);
				$sheet->getColumnDimension('G')->setWidth(15);

				$sheet->SetCellValue('H1', 'description');
				$sheet->getStyle('H1')->getFont()->setBold(true);
				$sheet->getColumnDimension('H')->setWidth(15);

				$sheet->SetCellValue('I1', 'change_type');
				$sheet->getStyle('I1')->getFont()->setBold(true);
				$sheet->getColumnDimension('I')->setWidth(15);

				$sheet->SetCellValue('J1', 'created_by');
				$sheet->getStyle('J1')->getFont()->setBold(true);
				$sheet->getColumnDimension('J')->setWidth(15);

				$sheet->SetCellValue('K1', 'submission_date');
				$sheet->getStyle('K1')->getFont()->setBold(true);
				$sheet->getColumnDimension('K')->setWidth(15);

				$sheet->SetCellValue('L1', 'approved_by');
				$sheet->getStyle('L1')->getFont()->setBold(true);
				$sheet->getColumnDimension('L')->setWidth(15);

				$sheet->SetCellValue('M1', 'approval_date');
				$sheet->getStyle('M1')->getFont()->setBold(true);
				$sheet->getColumnDimension('M')->setWidth(15);

				//start counter
				$i = 1;

				$HistoryTechnical = HistoryTechnical::all();

				foreach($HistoryTechnical as $row) {

					$i++;

					$sheet->setCellValueExplicit('A' . $i, $row['changerequest_id'])
						  ->setCellValueExplicit('B' . $i, $row['template_id'])
						  ->setCellValueExplicit('C' . $i, $row['row_code'])
						  ->setCellValueExplicit('D' . $i, $row['column_code'])
						  ->setCellValueExplicit('G' . $i, $row['content'])
						  ->setCellValueExplicit('H' . $i, $row['description'])
						  ->setCellValueExplicit('I' . $i, $row['change_type'])
						  ->setCellValueExplicit('K' . $i, $row['submission_date'])
						  ->setCellValueExplicit('M' . $i, $row['created_at']);

					//query for user table
					$created_by = User::where('id', $row['created_by'])->first();
					if (!empty($created_by)) {
						$sheet->setCellValueExplicit('J' . $i, $created_by['username']);
					}

					//query for user table
					$approved_by = User::where('id', $row['approved_by'])->first();
					if (!empty($approved_by)) {
						$sheet->setCellValueExplicit('L' . $i, $approved_by['username']);
					}

					//query for technical source table
					$source_id  = TechnicalSource::where('id', $row['source_id'])->first();
					if (!empty($source_id )) {
						$sheet->setCellValueExplicit('E' . $i, $source_id['source_name']);
					}

					//query for technical type table
					$type_id   = TechnicalType::where('id', $row['type_id'])->first();
					if (!empty($type_id )) {
						$sheet->setCellValueExplicit('F' . $i, $type_id['type_name']);
					}
				}

			});

		})->download('xlsx');
    }
}
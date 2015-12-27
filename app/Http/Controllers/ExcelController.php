<?php

namespace App\Http\Controllers;
use DB;
use App\Section;
use App\Template;
use App\TemplateRow;
use App\TemplateColumn;
use App\TemplateField;
use App\Requirement;

use App\User;
use App\UserRights;
use Gate;
use Auth;

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

use Event;
use App\Events\ChangeEvent;


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

	//TODO, use a blade instead of Controller debugging output
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
			$file = array('excel' => Input::file('excel'));

			//create an empty array for the structure
			$templatestructure = array();
			
			//create an empty array to capture validation problems
			$errors = array();

			$validation = Excel::load(Input::file('excel'), function ($reader) use ($request, &$templatestructure, &$errors) {

				// Getting all results
				$results = $reader->get();

				foreach($results as $sheet)
				{

					$worksheetTitle = $sheet->getTitle();
					$arraySheet = $sheet->toArray();

					if ($worksheetTitle == "structure") {

						//get column and row count from imported excel
						$highestRow = count($arraySheet) + 1;

						if ($highestRow > 2) {

							$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
							$highestColumnIndex = count($arraySheet[0]) + 1;
							$nrColumns = ord($highestColumn) - 64;

							//create empty arrays for build structure and for validation
							$templatestructure = array();
							$templatecolumns = array();
							$templaterows = array();
							//start counting unique id for disabled cells in templatestructure array
							$disabledcount = 1;

							for ($row = 1; $row <= $highestRow; ++ $row) {
								echo '<tr>';
								for ($col = 1; $col < $highestColumnIndex; ++ $col) {

									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($col);
									$val = $reader->getExcel()->getSheet()->getCell($columnLetter . $row)->getValue();

									//1th row is where the column names are stored
									if ($row == 1) {
										//4th column is where column naming starts
										if ($col > 4) {
											$columnid = $col-3;
											//validate if column_description is not empty
											if (empty($val)) {
												$templatestructure['columns'][$columnid]['column_description'] = $val;
												$templatestructure['columns'][$columnid]['error'] = "1";
												array_push($errors, "empty column_description in template structure");
											} else {
												$templatestructure['columns'][$columnid]['column_description'] = $val;
											}
										}
									} 
									
									//2nd row is where the column numbers are stored
									if ($row == 2) {
										//4th column is where column numbering starts
										if ($col > 4) {
											$columnid = $col-3;
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
									} 
									
									//more than 2 rows and 1st column is where the row number is stored
									if ($row > 2 && $col == 1) {
										$rowid = $row-2;
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
									if ($row > 2 && $col == 2) {
										$rowid = $row-2;
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
									if ($row > 2 && $col == 3) {
										$rowid = $row-2;
										$templatestructure['rows'][$rowid]['row_style'] = $val;
									} 
									
									//more than 2 rows and 4th column is where the row reference is stored
									if ($row > 2 && $col == 4) {
										$rowid = $row-2;
										$templatestructure['rows'][$rowid]['row_reference'] = $val;
									} 
									
									//more than 2 rows and 2 columns is where the disabled cells might be stored
									if ($row > 2 && $col > 4) {

										$newcol = $col-3;
										$newrow = $row-2;

										//todo update with cell color
										$cellcolor = '';

										//set cell column num and row num based on templatestructure
										$cell_column_code = $templatestructure['columns'][$newcol]['column_code'];
										$cell_row_code = $templatestructure['rows'][$newrow]['row_code'];
										//check if cell color is disabled: = D3D3D3
										if ($cellcolor == 'D3D3D3' || $val == 'disabled') {
											$templatestructure['disabledcells'][$disabledcount]['column_code'] = $cell_column_code;
											$templatestructure['disabledcells'][$disabledcount]['row_code'] = $cell_row_code;
											$disabledcount++;
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

						//get column and row count from imported excel
						$highestRow         = count($arraySheet) + 1;

						if ($highestRow > 1) {

							$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
							$highestColumnIndex = count($arraySheet[0]) + 1;
							$nrColumns = ord($highestColumn) - 64;

							//start counting unique id for column_content in templatestructure array
							$columncontentcount = 0;

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($col = 1; $col < $highestColumnIndex; ++ $col) {
									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($col);
									$val = $reader->getExcel()->getSheet(1)->getCell($columnLetter . $row)->getValue();
									
									//1th row is the heading
									if ($row == 1) {
										//validate if heading is correct
										if ($col == 1 && $val != 'number' || $col == 2 && $val != 'content_type' || $col == 3 && $val != 'content') {
											array_push($errors, "incorrect heading on column content sheet");
										}
									} 

									if ($row > 1) {
										//validate if content_type contains legal_desc or interpretation_desc
										if ($col == 2 && !($val == 'regulation' || $val == 'interpretation' || $val == 'reference')) {
											$templatestructure['column_content'][$columncontentcount]['content_type'] = $val;
											$templatestructure['column_content'][$columncontentcount]['error'] = "1";
											array_push($errors, "content_type not a valid value");
										//validate if column number exists in template columns
										} elseif ($col == 1 && !(in_array($val, $templatecolumns, true))) {
											$templatestructure['column_content'][$columncontentcount]['column_code'] = $val;
											$templatestructure['column_content'][$columncontentcount]['error'] = "1";
											array_push($errors, "column_code cannot be found in template structure");
										} else {
											//add content to template structure column_content
											if ($col == 1) {
												$templatestructure['column_content'][$columncontentcount]['column_code'] = $val;
											}
											if ($col == 2) {
												$templatestructure['column_content'][$columncontentcount]['content_type'] = $val;
											}
											if ($col == 3) {
												$templatestructure['column_content'][$columncontentcount]['content'] = $val;
											}
										}
									}
								}
								$columncontentcount++;
							}
						}
					}

					//validatie work sheet with the name row_content
					if ($worksheetTitle == "row_content") {
						//get column and row count from imported excel
						$highestRow         = count($arraySheet) + 1;

						if ($highestRow > 1) {

							$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
							$highestColumnIndex = count($arraySheet[0]) + 1;
							$nrColumns = ord($highestColumn) - 64;
							//start counting unique id for row_content in templatestructure array
							$rowcontentcount = 0;

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($col = 1; $col < $highestColumnIndex; ++ $col) {
									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($col);
									$val = $reader->getExcel()->getSheet(2)->getCell($columnLetter . $row)->getValue();
									//1th row is the heading
									if ($row == 1) {
										//validate if heading is correct
										if ($col == 1 && $val != 'number' || $col == 2 && $val != 'content_type' || $col == 3 && $val != 'content') {
											array_push($errors, "incorrect heading on row content sheet");
										}
									}
									
									if ($row > 1) {
										//validate if content_type contains legal_desc or interpretation_desc
										if ($col == 2 && !($val == 'regulation' || $val == 'interpretation' || $val == 'reference')) {
											$templatestructure['row_content'][$rowcontentcount]['content_type'] = $val;
											$templatestructure['row_content'][$rowcontentcount]['error'] = "1";
											array_push($errors, "content_type not a valid value");
										//validate if row number exists in templaterow
										} elseif ($col == 1 && !(in_array($val, $templaterows, true))) {
											$templatestructure['row_content'][$rowcontentcount]['row_code'] = $val;
											$templatestructure['row_content'][$rowcontentcount]['error'] = "1";
											array_push($errors, "row_code cannot be found in template structure");
										} else {
											//add content to templatestructure row_content
											if ($col == 1) {
												$templatestructure['row_content'][$rowcontentcount]['row_code'] = $val;
											}
											if ($col == 2) {
												$templatestructure['row_content'][$rowcontentcount]['content_type'] = $val;
											}
											if ($col == 3) {
												$templatestructure['row_content'][$rowcontentcount]['content'] = $val;
											}
										}
									}
								}
								$rowcontentcount++;
							}
						}
					}

					//validatie work sheet with the name column_content
					if ($worksheetTitle == "template_content") {
						//get column and row count from imported excel
						$highestRow         = count($arraySheet) + 1;
						$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
						$highestColumnIndex = count($arraySheet[0]) + 1;
						$nrColumns = ord($highestColumn) - 64;
						//start counting unique id for column_content in templatestructure array
						$columncontentcount = 0;

						for ($row = 1; $row <= $highestRow; ++ $row) {

							for ($col = 1; $col < $highestColumnIndex; ++ $col) {
								//set column letter and retrieve value
								$columnLetter = $this->getExcelColumnNumber($col);
								$val = $reader->getExcel()->getSheet(5)->getCell($columnLetter . $row)->getValue();
								//1th row is the heading
								if ($row == 1 && $col < 3) {
									if (empty($val)) {
										array_push($errors, "empty header on the template_content sheet");
									}
								}
								//next rows contain content
								//check content type
								if ($row == 2 && $col == 1) {
									if ($col == 1 && $val != 'template_longdesc') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "template_content is not correctly set");
									}
								}
								//add content to array
								if ($row == 2 && $col == 2) {
									$templatestructure['template_content']['template_longdesc'] = $val;
								}
								//check content type
								if ($row == 3 && $col == 1) {
									if ($col == 1 && $val != 'frequency_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "frequency_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 3 && $col == 2) {
									$templatestructure['template_content']['frequency_description'] = $val;
								}
								//check content type
								if ($row == 4 && $col == 1) {
									if ($col == 1 && $val != 'reporting_dates_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "reporting_dates_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 4 && $col == 2) {
									$templatestructure['template_content']['reporting_dates_description'] = $val;
								}
								//check content type
								if ($row == 5 && $col == 1) {
									if ($col == 1 && $val != 'main_changes_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "main_changes_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 5 && $col == 2) {
									$templatestructure['template_content']['main_changes_description'] = $val;
								}
								//check content type
								if ($row == 6 && $col == 1) {
									if ($col == 1 && $val != 'links_other_temp_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "links_other_temp_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 6 && $col == 2) {
									$templatestructure['template_content']['links_other_temp_description'] = $val;
								}
								//check content type
								if ($row == 7 && $col == 1) {
									if ($col == 1 && $val != 'process_and_organisation_description') {
										$templatestructure['template_content']['error'] = "1";
										array_push($errors, "process_and_organisation_description is not correctly set");
									}
								}
								//add content to array
								if ($row == 7 && $col == 2) {
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

						//get column and row count from imported excel
						$highestRow = count($arraySheet) + 1;

						if ($highestRow > 1) {

							$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
							$highestColumnIndex = count($arraySheet[0]) + 1;
							$nrColumns = ord($highestColumn) - 64;
							//start counting unique id for field_content in templatestructure array
							$sourcingcontentcount = 0;

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($col = 1; $col < $highestColumnIndex; ++ $col) {
									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($col);
									$val = $reader->getExcel()->getSheet(4)->getCell($columnLetter . $row)->getValue();
									//1th row is the heading
									if ($row == 1) {
										if ($col == 1 && $val != 'column_code' || $col == 2 && $val != 'row_code' || $col == 3 && $val != 'type' || $col == 4 && $val != 'source' || $col == 5 && $val != 'value' || $col == 6 && $val != 'description') {
											array_push($errors, "incorrect heading on sourcing sheet");
										}
									} 
									
									if ($row > 1) {
										//validate if column number exists in templatecolumns
										if ($col == 1 && !(in_array($val, $templatecolumns, true))) {
											$templatestructure['sourcing'][$sourcingcontentcount]['column_code'] = $val;
											$templatestructure['sourcing'][$sourcingcontentcount]['error'] = "1";
											array_push($errors, "column_code cannot be found in template structure");
										//validate if row number exists in templaterow
										} elseif ($col == 2 && !(in_array($val, $templaterows, true))) {
											$templatestructure['sourcing'][$sourcingcontentcount]['row_code'] = $val;
											$templatestructure['sourcing'][$sourcingcontentcount]['error'] = "1";
											array_push($errors, "row_code cannot be found in template structure");
										} elseif ($col == 3 && !(in_array($val, $type_array, true))) {
											$templatestructure['sourcing'][$sourcingcontentcount]['type'] = $val;
											$templatestructure['sourcing'][$sourcingcontentcount]['error'] = "1";
											array_push($errors, "type_name is not a valid value");
										} elseif ($col == 4 && !(in_array($val, $source_array, true))) {
											$templatestructure['sourcing'][$sourcingcontentcount]['source'] = $val;
											$templatestructure['sourcing'][$sourcingcontentcount]['error'] = "1";
											array_push($errors, "source_name is not a valid value");
										} else {
											//add content to templatestructure sourcing
											if ($col == 1) {
												$templatestructure['sourcing'][$sourcingcontentcount]['column_code'] = $val;
											}
											if ($col == 2) {
												$templatestructure['sourcing'][$sourcingcontentcount]['row_code'] = $val;
											}
											if ($col == 3) {
												$key = array_search($val, $type_array);
												$templatestructure['sourcing'][$sourcingcontentcount]['type'] = $key;
											}
											if ($col == 4) {
												$key = array_search($val, $source_array);
												$templatestructure['sourcing'][$sourcingcontentcount]['source'] = $key;
											}
											if ($col == 5) {
												$templatestructure['sourcing'][$sourcingcontentcount]['value'] = $val;
											}
											if ($col == 6) {
												$templatestructure['sourcing'][$sourcingcontentcount]['description'] = $val;
											}
										}
									}
								}
								$sourcingcontentcount++;
							}
						}
					}

					if ($worksheetTitle == "field_content") {
						//get column and row count from imported excel
						$highestRow         = count($arraySheet) + 1;

						if ($highestRow > 1) {

							$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
							$highestColumnIndex = count($arraySheet[0]) + 1;
							$nrColumns = ord($highestColumn) - 64;
							//start counting unique id for field_content in templatestructure array
							$fieldcontentcount = 0;

							for ($row = 1; $row <= $highestRow; ++ $row) {
								for ($col = 1; $col < $highestColumnIndex; ++ $col) {
									//set column letter and retrieve value
									$columnLetter = $this->getExcelColumnNumber($col);
									$val = $reader->getExcel()->getSheet(3)->getCell($columnLetter . $row)->getValue();
									//1th row is the heading
									if ($row == 1) {
										if ($col == 1 && $val != 'column_code' || $col == 2 && $val != 'row_code' || $col == 3 && $val != 'content_type' || $col == 4 && $val != 'content') {
											array_push($errors, "incorrect heading on field content sheet");
										}
									} 
									
									if ($row > 1) {
										//validate if content_type contains regulation or interpretation
										if ($col == 3 && !($val == 'regulation' || $val == 'interpretation' || $val == 'property1' || $val == 'property2')) {
											$templatestructure['field_content'][$fieldcontentcount]['content_type'] = $val;
											$templatestructure['field_content'][$fieldcontentcount]['error'] = "1";
											array_push($errors, "incorrect content_type used");
										//validate if column number exists in templatecolumns
										} elseif ($col == 1 && !(in_array($val, $templatecolumns, true))) {
											$templatestructure['field_content'][$fieldcontentcount]['column_code'] = $val;
											$templatestructure['field_content'][$fieldcontentcount]['error'] = "1";
											array_push($errors, "column_code cannot be found in template structure");
										//validate if row number exists in templaterow
										} elseif ($col == 2 && !(in_array($val, $templaterows, true))) {
											$templatestructure['field_content'][$fieldcontentcount]['row_code'] = $val;
											$templatestructure['field_content'][$fieldcontentcount]['error'] = "1";
											array_push($errors, "row_code cannot be found in template structure");
										} else {
											//add content to templatestructure field_content
											if ($col == 1) {
												$templatestructure['field_content'][$fieldcontentcount]['column_code'] = $val;
											}
											if ($col == 2) {
												$templatestructure['field_content'][$fieldcontentcount]['row_code'] = $val;
											}
											if ($col == 3) {
												$templatestructure['field_content'][$fieldcontentcount]['content_type'] = $val;
											}
											if ($col == 4) {
												$templatestructure['field_content'][$fieldcontentcount]['content'] = $val;
											}
										}
									}
								}
								$fieldcontentcount++;
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
					echo "Error: a template needs a least one column or one row!";
					exit();
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
						$templatefield = new TemplateField;
						$templatefield->template_id = $template->id;
						$templatefield->row_code = $field_content['row_code'];
						$templatefield->column_code = $field_content['column_code'];
						$templatefield->property = $field_content['content_type'];
						$templatefield->content = $field_content['content'];
						$templatefield->save();

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
						$templaterequirement->field_id = 'R-' . $requirement['row_code'];
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
						$templaterequirement->field_id = 'C-' . $requirement['column_code'];
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
						$templatefield = new TemplateField;
						$templatefield->template_id = $template->id;
						$templatefield->row_code = $disabledcell['row_code'];
						$templatefield->column_code = $disabledcell['column_code'];
						$templatefield->property = 'disabled';
						$templatefield->save();
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
				
				//log Event
				$event = array(
					"log_event" => "Template Excel",
					"action" => "created",
					"section_id" => $request->input('section_id'),
					"template_id" => $template->id,
					"created_by" => Auth::user()->id
				);
				
				Event::fire(new ChangeEvent($event));
				
				return Redirect::to('/sections');
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

				$disabled = TemplateField::where('template_id', $template->id)->where('property', 'disabled')->get();

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

				$column_content  = Requirement::where('template_id', $id)->where('field_id', 'LIKE', 'C-%')->where('content', '!=' , '')->orderBy('field_id', 'asc')->get();

				$columncontentcount = 2;
				//add content to excel
				if (!empty($column_content )) {
					foreach($column_content  as $key => $value) {
						$column_code = trim($value['field_id']);
						$column_code = ltrim($column_code, 'C-');
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

				$row_contents  = Requirement::where('template_id', $id)->where('field_id', 'LIKE', 'R-%')->where('content', '!=' , '')->orderBy('field_id', 'asc')->get();

				$rowcontentcount = 2;

				//add content to excel
				if (!empty($row_contents)) {
					foreach($row_contents as $key => $value) {
						$row_code = trim($value['field_id']);
						$row_code = ltrim($row_code, 'R-');
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

				$field_contents   = TemplateField::where('template_id', $id)->where('property', '!=' , 'disabled')->orderBy('row_code', 'asc')->orderBy('column_code', 'asc')->get();

				$fieldcontentcount = 2;
				//set grey fields, add two to put it correctly in the template
				if (!empty($field_contents)) {
					foreach($field_contents as $key => $value) {
						$sheet->setCellValueExplicit('A' . $fieldcontentcount, $value['column_code'])
						->setCellValueExplicit('B' . $fieldcontentcount, $value['row_code'])
						->setCellValueExplicit('C' . $fieldcontentcount, $value['property'])
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

					$sheet->setCellValueExplicit('A' . $i, $row['id'])
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
					$source_id  = TechnicalSource::where('id ', $row['source_id '])->first();
					if (!empty($source_id )) {
						$sheet->setCellValueExplicit('E' . $i, $source_id['source_name']);
					}

					//query for technical type table
					$type_id   = TechnicalType::where('id ', $row['type_id '])->first();
					if (!empty($type_id )) {
						$sheet->setCellValueExplicit('F' . $i, $type_id['type_name']);
					}
				}

			});

		})->download('xlsx');
    }
}

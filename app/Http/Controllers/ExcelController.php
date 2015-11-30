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
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Session;

class ExcelController extends Controller
{
	
	public function getExcelColumnNumber($num) 
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
	
	public function uploadexcel(Request $request) 
	{
		if ($request->isMethod('post')) {
		
			if ($request->hasFile('excel')) {
				if ($request->file('excel')->isValid()) {
					$file = array('excel' => Input::file('excel'));
					
					echo "<h2>Excel import section_id: " . $request->input('section_id') . "</h2>";
					
					if ($request->has('template_name')) {
						echo "<h2>Excel import template: " . $request->input('template_name') . "</h2>";
					} else {
						echo "Error: no template name entered!";
						exit();
					}
					
					if ($request->has('template_description')) {
						echo "<h3>Template description: " . $request->input('template_description') . "</h3>";
					}
								
					Excel::load(Input::file('excel'), function ($reader) use ($request) {

						// Getting all results
						$results = $reader->get();

						//set error to zero
						$error = 0;
					
						foreach($results as $sheet)
						{

							$worksheetTitle = $sheet->getTitle();							
							$arraySheet = $sheet->toArray();
							
							if ($worksheetTitle == "structure") {
								echo "<strong>Template structure</strong>";
								
								//get column and row count from imported excel
								$highestRow         = count($arraySheet) + 1;
								
								if ($highestRow > 2) {								
									
									$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
									$highestColumnIndex = count($arraySheet[0]) + 1;
									$nrColumns = ord($highestColumn) - 64;
									
									echo "<br><small>The worksheet ".$worksheetTitle." has ";
									echo $nrColumns . ' columns (A-' . $highestColumn . ') ';
									echo ' and ' . $highestRow . ' row.</small><br>';
									//create empty arrays for build structure and for validation
									$templatestructure = array();
									$templatecolumns = array();
									$templaterows = array();
									//start counting unique id for disabled cells in templatestructure array
									$disabledcount = 1;
									
									//create table
									echo '<table border="1">';

									for ($row = 1; $row <= $highestRow; ++ $row) {
										echo '<tr>';
										for ($col = 1; $col < $highestColumnIndex; ++ $col) {

											//set column letter and retrieve value
											$columnLetter = $this->getExcelColumnNumber($col);
											$val = $reader->getExcel()->getSheet()->getCell($columnLetter . $row)->getValue();
											
											//1th row is where the column names are stored
											if ($row == 1) {
												echo '<td style="background-color: #dff0d8; padding: 5px; font-weight: bold;">' . $val . '</td>';
												//4th column is where column naming starts
												if ($col > 4) {
													$columnid = $col-3;
													$templatestructure['columns'][$columnid]['column_name'] = $val;
												}
											//2nd row is where the column numbers are stored
											} elseif ($row == 2) {
												echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
												//4th column is where column numbering starts
												if ($col > 4) {
													$columnid = $col-3;
													$templatestructure['columns'][$columnid]['column_num'] = $val;
													//push to array for validation
													array_push($templatecolumns, $val);
												}
											//more than 2 rows and 1st column is where the row number is stored
											} elseif ($row > 2 && $col == 1) {
												echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
												$rowid = $row-2;
												$templatestructure['rows'][$rowid]['row_num'] = $val;
												//push to array for validation
												array_push($templaterows, $val);
											//more than 2 rows and 2nd column is where the row name is stored
											} elseif ($row > 2 && $col == 2) {
												echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
												$rowid = $row-2;
												$templatestructure['rows'][$rowid]['row_name'] = $val;
											//more than 2 rows and 3th column is where the row style is stored
											} elseif ($row > 2 && $col == 3) {
												echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
												$rowid = $row-2;
												$templatestructure['rows'][$rowid]['row_style'] = $val;
											//more than 2 rows and 4th column is where the row reference is stored
											} elseif ($row > 2 && $col == 4) {
												echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
												$rowid = $row-2;
												$templatestructure['rows'][$rowid]['row_reference'] = $val;
											//more than 2 rows and 2 columns is where the data is stored
											} elseif ($row > 2 && $col > 4) {

												$newcol = $col-3;
												$newrow = $row-2;
												
												//todo update with cell color
												$cellcolor = '';
												
												//set cell column num and row num based on templatestructure
												$cell_column_num = $templatestructure['columns'][$newcol]['column_num'];
												$cell_row_num = $templatestructure['rows'][$newrow]['row_num'];
												//check if cell color is disabled: = D3D3D3
												if ($cellcolor == 'D3D3D3' || $val == 'disabled') {
													echo '<td style="background-color: LightGray ! important; padding: 5px;">disabled</td>';
													$templatestructure['disabledcells'][$disabledcount]['column_num'] = $cell_column_num;
													$templatestructure['disabledcells'][$disabledcount]['row_num'] = $cell_row_num;
													$disabledcount++;
												} else {
													echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
												}
											}
											
											echo '</td>';
										}
										echo '</tr>';
									}
									
									echo '</table><br><br>';
								} else {
									$error = 1;
								}
								
							}
							
							//validatie work sheet with the name column_content
							if ($worksheetTitle == "column_content") {
								echo "<strong>Column content</strong><br><br>";
								//get column and row count from imported excel
								$highestRow         = count($arraySheet) + 1;
								
								if ($highestRow > 1) {
									
									$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
									$highestColumnIndex = count($arraySheet[0]) + 1;
									$nrColumns = ord($highestColumn) - 64;
									
									//start counting unique id for column_content in templatestructure array
									$columncontentcount = 0;
									//create table
									echo '<table border="1" style="max-width: 90%;">';
									for ($row = 1; $row <= $highestRow; ++ $row) {
										echo '<tr>';
										for ($col = 1; $col < $highestColumnIndex; ++ $col) {
											//set column letter and retrieve value
											$columnLetter = $this->getExcelColumnNumber($col);
											$val = $reader->getExcel()->getSheet(1)->getCell($columnLetter . $row)->getValue();
											//1th row is the heading
											if ($row == 1) {
												//validate if heading is correct
												if ($col == 1 && $val != 'number' || $col == 2 && $val != 'content_type' || $col == 3 && $val != 'content') {
													echo '<td style="background-color: #FFB2B2; padding: 5px; font-weight: bold;">' . $val . '</td>';
												} else {
													echo '<td style="background-color: #dff0d8; padding: 5px; font-weight: bold;">' . $val . '</td>';
												}
											} else {
												//validate if content_type contains legal_desc or interpretation_desc
												if ($col == 2 && !($val == 'legal_desc' || $val == 'interpretation_desc' || $val == 'reference')) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
												//validate if column number exists in template columns
												} elseif ($col == 1 && !(in_array($val, $templatecolumns))) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												} else {
													echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
													//add content to template structure column_content
													if ($col == 1) {
														$templatestructure['column_content'][$columncontentcount]['number'] = $val;
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
										echo '</tr>';
									}
									echo '</table><br>';
								}
							}
							
							//validatie work sheet with the name row_content
							if ($worksheetTitle == "row_content") {
								echo "<strong>Row content</strong><br><br>";
								//get column and row count from imported excel
								$highestRow         = count($arraySheet) + 1;
								
								if ($highestRow > 1) {
									
									$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
									$highestColumnIndex = count($arraySheet[0]) + 1;
									$nrColumns = ord($highestColumn) - 64;
									//start counting unique id for row_content in templatestructure array
									$rowcontentcount = 0;
									//create table
									echo '<table border="1" style="max-width: 90%;">';
									for ($row = 1; $row <= $highestRow; ++ $row) {
										echo '<tr>';
										for ($col = 1; $col < $highestColumnIndex; ++ $col) {
											//set column letter and retrieve value
											$columnLetter = $this->getExcelColumnNumber($col);
											$val = $reader->getExcel()->getSheet(2)->getCell($columnLetter . $row)->getValue();
											//1th row is the heading
											if ($row == 1) {
												//validate if heading is correct
												if ($col == 1 && $val != 'number' || $col == 2 && $val != 'content_type' || $col == 3 && $val != 'content') {
													echo '<td style="background-color: #FFB2B2; padding: 5px; font-weight: bold;">' . $val . '</td>';
												} else {
													echo '<td style="background-color: #dff0d8; padding: 5px; font-weight: bold;">' . $val . '</td>';
												}
											} else {
												//validate if content_type contains legal_desc or interpretation_desc
												if ($col == 2 && !($val == 'legal_desc' || $val == 'interpretation_desc' || $val == 'reference')) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
												//validate if row number exists in templaterow
												} elseif ($col == 1 && !(in_array($val, $templaterows))) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												} else {
													echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
													//add content to templatestructure row_content
													if ($col == 1) {
														$templatestructure['row_content'][$rowcontentcount]['number'] = $val;
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
										echo '</tr>';
									}
									echo '</table><br><br>';
								}
							}
							
							//validatie work sheet with the name column_content
							if ($worksheetTitle == "template_content") {
								echo "<strong>Template content</strong><br><br>";
								//get column and row count from imported excel
								$highestRow         = count($arraySheet) + 1;
								$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
								$highestColumnIndex = count($arraySheet[0]) + 1;
								$nrColumns = ord($highestColumn) - 64;
								//start counting unique id for column_content in templatestructure array
								$columncontentcount = 0;
								//create table
								echo '<table border="1" style="max-width: 90%;">';
								for ($row = 1; $row <= $highestRow; ++ $row) {
									echo '<tr>';
									for ($col = 1; $col < $highestColumnIndex; ++ $col) {
										//set column letter and retrieve value
										$columnLetter = $this->getExcelColumnNumber($col);
										$val = $reader->getExcel()->getSheet(5)->getCell($columnLetter . $row)->getValue();
										//1th row is the heading
										if ($row == 1 && $col < 3) {
											echo '<td style="background-color: #dff0d8; padding: 5px; font-weight: bold;">'. $val .'</td>';
										}
										//next rows contain content
										//check content type
										if ($row == 2 && $col == 1) {
											if ($col == 1 && $val != 'template_longdesc') {
												echo '<td style="background-color: #FFB2B2; padding: 5px;">'. $val .'</td>';
												$error = 1;
											} else {
												echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											}
										}
										//add content to array
										if ($row == 2 && $col == 2) {
											echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											$templatestructure['template_content']['template_longdesc'] = $val;
										}
										//check content type
										if ($row == 3 && $col == 1) {
											if ($col == 1 && $val != 'frequency_description') {
												echo '<td style="background-color: #FFB2B2; padding: 5px;">'. $val .'</td>';
												$error = 1;
											} else {
												echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											}
										}
										//add content to array
										if ($row == 3 && $col == 2) {
											echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											$templatestructure['template_content']['frequency_description'] = $val;
										}
										//check content type
										if ($row == 4 && $col == 1) {
											if ($col == 1 && $val != 'reporting_dates_description') {
												echo '<td style="background-color: #FFB2B2; padding: 5px;">'. $val .'</td>';
												$error = 1;
											} else {
												echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											}
										}
										//add content to array
										if ($row == 4 && $col == 2) {
											echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											$templatestructure['template_content']['reporting_dates_description'] = $val;
										}
										//check content type
										if ($row == 5 && $col == 1) {
											if ($col == 1 && $val != 'main_changes_description') {
												echo '<td style="background-color: #FFB2B2; padding: 5px;">'. $val .'</td>';
												$error = 1;
											} else {
												echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											}
										}
										//add content to array
										if ($row == 5 && $col == 2) {
											echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											$templatestructure['template_content']['main_changes_description'] = $val;
										}
										//check content type
										if ($row == 6 && $col == 1) {
											if ($col == 1 && $val != 'links_other_temp_description') {
												echo '<td style="background-color: #FFB2B2; padding: 5px;">'. $val .'</td>';
												$error = 1;
											} else {
												echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											}
										}
										//add content to array
										if ($row == 6 && $col == 2) {
											echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											$templatestructure['template_content']['links_other_temp_description'] = $val;
										}
										//check content type
										if ($row == 7 && $col == 1) {
											if ($col == 1 && $val != 'process_and_organisation_description') {
												echo '<td style="background-color: #FFB2B2; padding: 5px;">'. $val .'</td>';
												$error = 1;
											} else {
												echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											}
										}
										//add content to array
										if ($row == 7 && $col == 2) {
											echo '<td style="background-color: #FFF; padding: 5px;">'. $val .'</td>';
											$templatestructure['template_content']['process_and_organisation_description'] = $val;
										}
									}
									echo '</tr>';
								}
								echo '</table><br><br>';
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
							
								echo "<strong>Sourcing content</strong><br><br>";
								//get column and row count from imported excel
								$highestRow         = count($arraySheet) + 1;
								
								if ($highestRow > 1) {
								
									$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
									$highestColumnIndex = count($arraySheet[0]) + 1;
									$nrColumns = ord($highestColumn) - 64;
									//start counting unique id for field_content in templatestructure array
									$sourcingcontentcount = 0;
									//create table
									echo '<table border="1" style="max-width: 90%;">';
									for ($row = 1; $row <= $highestRow; ++ $row) {
										echo '<tr>';
										for ($col = 1; $col < $highestColumnIndex; ++ $col) {
											//set column letter and retrieve value
											$columnLetter = $this->getExcelColumnNumber($col);
											$val = $reader->getExcel()->getSheet(4)->getCell($columnLetter . $row)->getValue();
											//1th row is the heading
											if ($row == 1) {
												if ($col == 1 && $val != 'column_number' || $col == 2 && $val != 'row_number' || $col == 3 && $val != 'type' || $col == 4 && $val != 'source' || $col == 5 && $val != 'value' || $col == 6 && $val != 'description') {
													echo '<td style="background-color: #FFB2B2; padding: 5px; font-weight: bold;">' . $val . '</td>';
												} else {
													echo '<td style="background-color: #dff0d8; padding: 5px; font-weight: bold;">' . $val . '</td>';
												}
											} else {
												//validate if column number exists in templatecolumns
												if ($col == 1 && !(in_array($val, $templatecolumns))) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												//validate if row number exists in templaterow
												} elseif ($col == 2 && !(in_array($val, $templaterows))) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												} elseif ($col == 3 && !(in_array($val, $type_array))) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												} elseif ($col == 4 && !(in_array($val, $source_array))) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												} else {
													echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
													//add content to templatestructure sourcing
													if ($col == 1) {
														$templatestructure['sourcing'][$sourcingcontentcount]['column_number'] = $val;
													}
													if ($col == 2) {
														$templatestructure['sourcing'][$sourcingcontentcount]['row_number'] = $val;
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
										echo '</tr>';
									}
									echo '</table><br><br>';
								}
							}

							if ($worksheetTitle == "field_content") {
								echo "<strong>Field content</strong><br><br>";
								//get column and row count from imported excel
								$highestRow         = count($arraySheet) + 1;
								
								if ($highestRow > 1) {
									
									$highestColumn = $this->getExcelColumnNumber(count($arraySheet[0]));
									$highestColumnIndex = count($arraySheet[0]) + 1;
									$nrColumns = ord($highestColumn) - 64;
									//start counting unique id for field_content in templatestructure array
									$fieldcontentcount = 0;
									//create table
									echo '<table border="1" style="max-width: 90%;">';
									for ($row = 1; $row <= $highestRow; ++ $row) {
										echo '<tr>';
										for ($col = 1; $col < $highestColumnIndex; ++ $col) {
											//set column letter and retrieve value
											$columnLetter = $this->getExcelColumnNumber($col);
											$val = $reader->getExcel()->getSheet(3)->getCell($columnLetter . $row)->getValue();
											//1th row is the heading
											if ($row == 1) {
												if ($col == 1 && $val != 'column_number' || $col == 2 && $val != 'row_number' || $col == 3 && $val != 'content_type' || $col == 4 && $val != 'content') {
													echo '<td style="background-color: #FFB2B2; padding: 5px; font-weight: bold;">' . $val . '</td>';
												} else {
													echo '<td style="background-color: #dff0d8; padding: 5px; font-weight: bold;">' . $val . '</td>';
												}
											} else {
												//validate if content_type contains legal_desc or interpretation_desc
												if ($col == 3 && !($val == 'legal_desc' || $val == 'interpretation_desc' || $val == 'property1' || $val == 'property2')) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												//validate if column number exists in templatecolumns
												} elseif ($col == 1 && !(in_array($val, $templatecolumns))) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												//validate if row number exists in templaterow
												} elseif ($col == 2 && !(in_array($val, $templaterows))) {
													echo '<td style="background-color: #FFB2B2; padding: 5px;">' . $val . '</td>';
													$error = 1;
												} else {
													echo '<td style="background-color: #FAFAFA; padding: 5px;">' . $val . '</td>';
													//add content to templatestructure field_content
													if ($col == 1) {
														$templatestructure['field_content'][$fieldcontentcount]['column_number'] = $val;
													}
													if ($col == 2) {
														$templatestructure['field_content'][$fieldcontentcount]['row_number'] = $val;
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
										echo '</tr>';
									}
									echo '</table><br><br>';
								}
							}							
							
							
							
						}
						
						if ($error == 0) {
							if (!empty($templatestructure)) {
								//Restructuring of the array is needed because legal_desc, interpretation_desc and reference are stored in the database on a single line
								//The code below joins the legal_desc, interpretation_desc and reference for the row and column lines
								if (!empty($templatestructure['column_content'])) {
									foreach($templatestructure['column_content'] as $content_c) {
										//number could be blank if row of column cannot be found in template structure
										if (isset($content_c['number'])) {
											$c_number = 'C-' . $content_c['number'];
											$c_content_type = $content_c['content_type'];
											if (!isset($templatestructure['requirements'][$c_number][$c_content_type])) {
												$templatestructure['requirements'][$c_number][$c_content_type] = $content_c['content'];
											} else {
												echo "<p style=\"color:red;\"><strong>Error: </strong>" . $c_number . ' - ' . $c_content_type . ' already exists!</p>';
												$error = 1;
											}
										}
									}
								}
								if (!empty($templatestructure['row_content'])) {
									foreach($templatestructure['row_content'] as $content_r) {
										//number could be blank if row of column cannot be found in template structure
										if (isset($content_r['number'])) {
											$r_number = 'R-' . $content_r['number'];
											$r_content_type = $content_r['content_type'];
											if (!isset($templatestructure['requirements'][$r_number][$r_content_type])) {
												$templatestructure['requirements'][$r_number][$r_content_type] = $content_r['content'];
											} else {
												echo "<p style=\"color:red;\"><strong>Error: </strong>" . $r_number . ' - ' . $r_content_type . ' already exists!</p>';
												$error = 1;
											}
										}
									}
								}
							}
						}
						
						//add new template to database
						if ($request->has('section_id')) {
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
							
							//add template column to database
							if (!empty($templatestructure['columns'])) {
								$i = 1;
								foreach($templatestructure['columns'] as $columnline) {
									$column = new TemplateColumn;
									$column->template_id = $template->id;
									$column->column_num = $i;
									$column->column_name = $columnline['column_num'];
									$column->column_description = $columnline['column_name'];
									$column->save();
									$i++;
								}
							//a template needs a least one column
							} else {
								exit();
							}
							
							//add template rows to database
							if (!empty($templatestructure['rows'])) {
								$i = 1;
								foreach($templatestructure['rows'] as $rowline) {
									$row = new TemplateRow;
									$row->template_id = $template->id;
									$row->row_num = $i;
									$row->row_name = $rowline['row_num'];
									$row->row_description = $rowline['row_name'];
									$row->row_reference = $rowline['row_reference'];
									$row->save();
									$i++;
								}
							//a template needs a least one row
							} else {
								exit();
							}
							
							//add template fields to database
							if (!empty($templatestructure['field_content'])) {
								foreach($templatestructure['field_content'] as $field_content) {
									$templatefield = new TemplateField;
									$templatefield->template_id = $template->id;
									$templatefield->row_name = $field_content['row_number'];
									$templatefield->column_name = $field_content['column_number'];
									$templatefield->property = $field_content['content_type'];
									$templatefield->content = $field_content['content'];
									$templatefield->save();
								}
							}
							
							//add template requirements to database
							if (!empty($templatestructure['requirements'])) {
								foreach($templatestructure['requirements'] as $key => $requirement) {
									if (!empty($requirement['reference'])) {
										$field_refence = $requirement['reference'];
									} else {
										$field_refence = NULL;
									}
									if (!empty($requirement['legal_desc'])) {
										$field_legal_desc = $requirement['legal_desc'];
									} else {
										$field_legal_desc = NULL;
									}
									if (!empty($requirement['interpretation_desc'])) {
										$field_interpretation_desc = $requirement['interpretation_desc'];
									} else {
										$field_interpretation_desc = NULL;
									}
									$templaterequirement = new Requirement;
									$templaterequirement->template_id = $template->id;
									$templaterequirement->field_id = $key;
									$templaterequirement->reference = $field_refence;
									$templaterequirement->legal_desc = $field_legal_desc;
									$templaterequirement->interpretation_desc = $field_interpretation_desc;
									$templaterequirement->save();
								}
							}
							
							//add disabled cells to database
							if (!empty($templatestructure['disabledcells'])) {
								foreach($templatestructure['disabledcells'] as $disabledcell) {
									$templatefield = new TemplateField;
									$templatefield->template_id = $template->id;
									$templatefield->row_name = $disabledcell['row_num'];
									$templatefield->column_name = $disabledcell['column_num'];
									$templatefield->property = 'disabled';
									$templatefield->save();
								}
							}
							
							//add technical content to database
							if (!empty($templatestructure['sourcing'])) {
								foreach($templatestructure['sourcing'] as $sourcing) {
									$technical = new Technical;
									$technical->template_id = $template->id;
									$technical->source_id = $sourcing['source'];
									$technical->type_id = $sourcing['type'];
									$technical->content = $sourcing['value'];
									$technical->row_num = $sourcing['row_number'];
									$technical->col_num = $sourcing['column_number'];
									$technical->description = $sourcing['description'];
									$technical->save();
								}
							}
						}
						
						//echo "<pre>";
						//print_r($templatecolumns);
						//echo "</pre>";	

						//echo "<pre>";
						//print_r($templaterows);
						//echo "</pre>";
						
						//echo "<pre>";
						//print_r($templatestructure);
						//echo "</pre>";
						
						//echo $template->id;
						
					});

				}
			
			}

			return Redirect::to('/sections');
		}
	}	

	//function to export template to excel
	public function export($id)
	{
	
		$template = Template::find($id);
	
		Excel::create($template->template_name, function($excel) use ($id)  {

			// Our first sheet
			$excel->sheet('structure', function($sheet) use ($id) {
			
				$template = Template::find($id);
			
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
					//add column id and row_name to structure
					$column_name = trim($column['column_name']);
					$templatestructure['columns'][$column_name] = $i;
					$i++;
					$letter++;
					$sheet->SetCellValue($letter . '1', $column['column_description']);
					$sheet->getStyle($letter . '1')->getFont()->setBold(true);
					$sheet->getColumnDimension($letter)->setWidth(20);
					$sheet->setCellValueExplicit($letter . '2', $column['column_name']);
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
					//add row id and row_name to structure
					$row_name = trim($row['row_name']);
					$templatestructure['rows'][$row_name] = $i;
					$i++;
					$sheet->getRowDimension($rownumber)->setRowHeight(20);
					//row_num data as string
					$sheet->setCellValueExplicit('A' . $rownumber, $row['row_name']);
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
						//get row_name and column_name from array
						$disabled_row_name = $disabledrows['row_name'];
						$disabled_column_name = $disabledrows['column_name'];
						//get row_name and column name from structure
						$structurerowid = $templatestructure['rows'][$disabled_row_name];
						$structurecolumnid = $templatestructure['columns'][$disabled_column_name];
						//jump in two from top and three from left
						$structurerowid = $structurerowid + 2;
						$structurecolumnid = $structurecolumnid + 3;
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
				
				$column_contents_legal  = Requirement::where('template_id', $id)->where('field_id', 'LIKE', 'C-%')->where('legal_desc', '!=' , '')->orderBy('field_id', 'asc')->get();
				
				$columncontentcount = 2;
				//add content to excel
				if (!empty($column_contents_legal )) {
					foreach($column_contents_legal  as $key => $value) {
						$column_name = trim($value['field_id']);
						$column_name = ltrim($column_name, 'C-');
						$sheet->setCellValueExplicit('A' . $columncontentcount, $column_name)
						->setCellValueExplicit('B' . $columncontentcount, 'legal_desc')
						->setCellValueExplicit('C' . $columncontentcount, $value['legal_desc']);
						$columncontentcount++;
					}
				}
				
				$column_contents_inter = Requirement::where('template_id', $id)->where('field_id', 'LIKE', 'C-%')->where('interpretation_desc', '!=' , '')->orderBy('field_id', 'asc')->get();

				//add content to excel
				if (!empty($column_contents_inter)) {
					foreach($column_contents_inter as $key => $value) {
						$column_name = trim($value['field_id']);
						$column_name = ltrim($column_name, 'C-');
						$sheet->setCellValueExplicit('A' . $columncontentcount, $column_name)
						->setCellValueExplicit('B' . $columncontentcount, 'interpretation_desc')
						->setCellValueExplicit('C' . $columncontentcount, $value['interpretation_desc']);
						$columncontentcount++;
					}
				}
				
				$column_contents_ref = Requirement::where('template_id', $id)->where('field_id', 'LIKE', 'C-%')->where('reference', '!=' , '')->orderBy('field_id', 'asc')->get();
				//add content to excel
				if (!empty($column_contents_ref)) {
					foreach($column_contents_ref as $key => $value) {
						$column_name = trim($value['field_id']);
						$column_name = ltrim($column_name, 'C-');
						$sheet->setCellValueExplicit('A' . $columncontentcount, $column_name)
						->setCellValueExplicit('B' . $columncontentcount, 'reference')
						->setCellValueExplicit('C' . $columncontentcount, $value['reference']);
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
				
				$row_contents_legal  = Requirement::where('template_id', $id)->where('field_id', 'LIKE', 'R-%')->where('legal_desc', '!=' , '')->orderBy('field_id', 'asc')->get();
				
				$rowcontentcount = 2;

				//add content to excel
				if (!empty($row_contents_legal)) {
					foreach($row_contents_legal as $key => $value) {
						$row_name = trim($value['field_id']);
						$row_name = ltrim($row_name, 'R-');
						$sheet->setCellValueExplicit('A' . $rowcontentcount, $row_name)
						->setCellValueExplicit('B' . $rowcontentcount, 'legal_desc')
						->setCellValueExplicit('C' . $rowcontentcount, $value['legal_desc']);
						$rowcontentcount++;
					}
				}
				
				$row_contents_inter = Requirement::where('template_id', $id)->where('field_id', 'LIKE', 'R-%')->where('interpretation_desc', '!=' , '')->orderBy('field_id', 'asc')->get();
				
				//add content to excel
				if (!empty($row_contents_inter)) {
					foreach($row_contents_inter as $key => $value) {
						$row_name = trim($value['field_id']);
						$row_name = ltrim($row_name, 'R-');
						$sheet->setCellValueExplicit('A' . $rowcontentcount, $row_name)
						->setCellValueExplicit('B' . $rowcontentcount, 'interpretation_desc')
						->setCellValueExplicit('C' . $rowcontentcount, $value['interpretation_desc']);
						$rowcontentcount++;
					}
				}
				
				$row_contents_ref = Requirement::where('template_id', $id)->where('field_id', 'LIKE', 'R-%')->where('reference', '!=' , '')->orderBy('field_id', 'asc')->get();
				
				//add content to excel
				if (!empty($row_contents_ref)) {
					foreach($row_contents_ref as $key => $value) {
						$row_name = trim($value['field_id']);
						$row_name = ltrim($row_name, 'R-');
						$sheet->setCellValueExplicit('A' . $rowcontentcount, $row_name)
						->setCellValueExplicit('B' . $rowcontentcount, 'reference')
						->setCellValueExplicit('C' . $rowcontentcount, $value['reference']);
						$rowcontentcount++;
					}
				}
				
			});	

			// Our fourth sheet
			$excel->sheet('field_content', function($sheet) use ($id) {
			
				//set first column for field_content
				//Column part
				$sheet->SetCellValue('A1', 'column_number');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(16);

				$sheet->SetCellValue('B1', 'row_number');
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
				
				$field_contents   = TemplateField::where('template_id', 1)->where('property', '!=' , 'disabled')->orderBy('row_name', 'asc')->orderBy('column_name', 'asc')->get();
				
				$fieldcontentcount = 2;
				//set grey fields, add two to put it correctly in the template
				if (!empty($field_contents)) {
					foreach($field_contents as $key => $value) {
						$sheet->setCellValueExplicit('A' . $fieldcontentcount, $value['column_name'])
						->setCellValueExplicit('B' . $fieldcontentcount, $value['row_name'])
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
				$sheet->SetCellValue('A1', 'column_number');
				$sheet->getStyle('A1')->getFont()->setBold(true);
				$sheet->getColumnDimension('A')->setWidth(16);

				$sheet->SetCellValue('B1', 'row_number');
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
						$sheet->setCellValueExplicit('A' . $fieldcontentcount, $value['col_num'])
						->setCellValueExplicit('B' . $fieldcontentcount, $value['row_num'])
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
	public function uploadform() 
	{
		$sections = Section::all();
		return view('excel.upload', compact('sections'));
	}
	
}

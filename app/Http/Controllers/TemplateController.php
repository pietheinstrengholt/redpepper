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
	
	//function to export template to excel
	public function export($id)
	{
	
		Excel::create('Filename', function($excel) {

			// Our first sheet
			$excel->sheet('structure', function($sheet) {
			
				$template = Template::find(13);
			
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
						$columnLetter = PHPExcel_Cell::stringFromColumnIndex($structurecolumnid);
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
			$excel->sheet('column_content', function($sheet) {
			
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
				
				$column_contents_legal  = Requirement::where('template_id', 13)->where('field_id', 'LIKE', 'C-%')->where('legal_desc', '!=' , '')->orderBy('field_id', 'asc')->get();
				
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
				
				$column_contents_inter = Requirement::where('template_id', 13)->where('field_id', 'LIKE', 'C-%')->where('interpretation_desc', '!=' , '')->orderBy('field_id', 'asc')->get();

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
				
				$column_contents_ref = Requirement::where('template_id', 13)->where('field_id', 'LIKE', 'C-%')->where('reference', '!=' , '')->orderBy('field_id', 'asc')->get();
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
			$excel->sheet('row_content', function($sheet) {
			
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
				
				$row_contents_legal  = Requirement::where('template_id', 13)->where('field_id', 'LIKE', 'R-%')->where('legal_desc', '!=' , '')->orderBy('field_id', 'asc')->get();
				
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
				
				$row_contents_inter = Requirement::where('template_id', 13)->where('field_id', 'LIKE', 'R-%')->where('interpretation_desc', '!=' , '')->orderBy('field_id', 'asc')->get();
				
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
				
				$row_contents_ref = Requirement::where('template_id', 13)->where('field_id', 'LIKE', 'R-%')->where('reference', '!=' , '')->orderBy('field_id', 'asc')->get();
				
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
			$excel->sheet('field_content', function($sheet) {
			
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
			$excel->sheet('sourcing', function($sheet) {

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
				
				$field_contents = Technical::where('template_id', 13)->get();
				
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
			$excel->sheet('template_content', function($sheet) {

				$template_content = Template::find(13);

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
			$excel->sheet('explanation', function($sheet) {

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
			'requirement_row' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'R-' . $rownum)->first(),
			'requirement_column' => Requirement::where('template_id', $_GET['template_id'])->where('field_id', 'C-' . $columnnum)->first(),
			'technical' => Technical::where('template_id', $_GET['template_id'])->where('row_num', $rownum)->where('col_num', $columnnum)->get(),
			'field_legal_desc' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'legal_desc')->get(),
			'field_interpretation_desc' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'interpretation_desc')->get(),
			'field_property1' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'property1')->get(),
			'field_property2' => TemplateField::where('template_id', $_GET['template_id'])->where('row_name', $rownum)->where('column_name', $columnnum)->where('property', 'property2')->get()
		]);
		
    }
	
}

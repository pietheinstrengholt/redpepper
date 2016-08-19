<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Term;
use App\TermProperty;
use App\Glossary;
use App\Ontology;
use App\Status;
use App\Relation;
use App\User;
use Gate;
use Auth;
use Illuminate\Http\Request;
use Redirect;
class TermPropertyController extends Controller
{
	public function index(Request $request)
	{
		$termproperties = TermProperty::select('property_name')->orderBy('property_name', 'asc')->distinct()->get();

		if ($request->has('property_name')) {
			$termvalues = TermProperty::where('property_name', $request->input('property_name'))->orderBy('property_value', 'asc')->get();
			$property_name = $request->input('property_name');
		} else {
			$termvalues = null;
			$property_name = null;
		}

		return view('termproperties.index', compact('termproperties','termvalues','property_name'));
	}
}

<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Ontology;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;
class OntologyController extends Controller
{
	public function apiIndex()
	{
		$ontologies = Ontology::orderBy('id', 'asc')->get();
		return response()->json($ontologies);
	}

	public function apiShow($id)
	{
		$ontology = Ontology::with('subject')->with('object')->with('status')->with('relation')->get()->find($id);
		return response()->json($ontology);
	}
}

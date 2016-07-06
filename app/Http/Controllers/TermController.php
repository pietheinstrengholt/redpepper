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
class TermController extends Controller
{
	public function index(Request $request)
	{
		$terms = Term::orderBy('term_name', 'asc')->get();
		
		//create an array with all first letters from all terms in the database, used for pagination
		$letters = array();
		if (!empty($terms)) {
			foreach ($terms as $term) {
				array_push($letters,substr($term->term_name, 0, 1));
			}
			$letters = array_unique($letters);
		}
		
		//if letters are not empty check if letter is set with argument, else take first letter from array
		if (!empty($letters)) {
			if ($request->has('letter')) {
				$terms = Term::orderBy('term_name', 'asc')->where('term_name', 'LIKE', $request->input('letter').'%')->get();
			} else {
				$terms = Term::orderBy('term_name', 'asc')->where('term_name', 'LIKE', $letters[0].'%')->get();
			}
		}

		return view('terms.index', compact('terms','letters'));
	}

	public function show(Term $term)
	{
		//check if id property exists
		if (!$term->id) {
			abort(403, 'This term no longer exists in the database.');
		}
		return view('terms.show', compact('term'));
	}

	public function edit(Term $term)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$owners = User::orderBy('username', 'asc')->get();
		$statuses = Status::orderBy('status_name', 'asc')->get();
		$glossaries = Glossary::orderBy('glossary_name', 'asc')->get();
		$relations = Relation::orderBy('relation_name', 'asc')->get();

		return view('terms.edit', compact('term','statuses','glossaries','relations','owners'));
	}

	public function create(Term $term)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$owners = User::orderBy('username', 'asc')->get();
		$statuses = Status::orderBy('status_name', 'asc')->get();
		$glossaries = Glossary::orderBy('glossary_name', 'asc')->get();
		$relations = Relation::orderBy('relation_name', 'asc')->get();

		return view('terms.create', compact('term','statuses','glossaries','relations','owners'));
	}

	public function store(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'term_name' => 'required|min:3',
			'term_description' => 'required',
			'glossary_id' => 'required',
			'status_id' => 'required',
			'owner_id' => 'required'
		]);
		Term::create($request->all());
		return Redirect::to('/terms?letter=' . substr($request->input('term_name'), 0, 1))->with('message', 'Term created.');
	}

	public function update(Term $term, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'term_id' => 'required',
			'term_name' => 'required|min:3',
			'term_description' => 'required',
			'glossary_id' => 'required',
			'status_id' => 'required',
			'owner_id' => 'required'
		]);

		//delete existing content
		Ontology::where('subject_id', $request->input('term_id'))->delete();

		foreach($request->input('Relations') as $relation) {
			if (!empty($relation['relation_id']) && !empty($relation['object_id'])) {
				$ontology = new Ontology;
				$ontology->subject_id = $request->input('term_id');
				$ontology->relation_id = $relation['relation_id'];
				$ontology->object_id = $relation['object_id'];
				$ontology->status_id = $request->input('status_id');
				$ontology->created_by = Auth::user()->id;
				$ontology->save();
			}
		}

		//detele existing term properties
		TermProperty::where('term_id', $request->input('term_id'))->delete();

		foreach($request->input('Properties') as $property) {
			if (!empty($property['property_name'])) {
				$termproperty = new TermProperty;
				$termproperty->term_id = $request->input('term_id');
				$termproperty->property_name = $property['property_name'];
				$termproperty->property_value = $property['property_value'];
				$termproperty->created_by = Auth::user()->id;
				$termproperty->save();
			}
		}

		$term->update($request->all());
		return Redirect::to('/terms?letter=' . substr($term->term_name, 0, 1))->with('message', 'Term updated.');
	}

	public function destroy(Term $term)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$term->id) {
			abort(403, 'This term no longer exists in the database.');
		}

		//delete ontology
		Ontology::where('subject_id', $term->id)->delete();
		Ontology::where('object_id', $term->id)->delete();

		$term->delete();
		return Redirect::to('/terms?letter=' . substr($term->term_name, 0, 1))->with('message', 'Term and related ontology deleted.');
	}

	public function apiIndex(Request $request)
	{
		if ($request->has('search')) {
			$terms = Term::where('term_name', 'like', '%' . $request->input('search') . '%')->orderBy('term_name', 'asc')->orWhere(function ($query) use ($request) {
				$query->where('term_description', 'like', '%' . $request->input('search') . '%')->orderBy('term_name', 'asc');
			})->get();
		} elseif ($request->has('property_name') && $request->has('property_value')) {
			$terms = Term::whereHas('properties', function ($query) use ($request) {
				$query->where('property_name', 'like', '%' . $request->input('property_name') . '%')->where('property_value', 'like', '%' . $request->input('property_value') . '%');
			})->orderBy('term_name', 'asc')->get();
		} elseif ($request->has('property_name')) {
			$terms = Term::whereHas('properties', function ($query) use ($request) {
				$query->where('property_name', 'like', '%' . $request->input('property_name') . '%');
			})->orderBy('term_name', 'asc')->get();
		} else {
			$terms = Term::orderBy('term_name', 'asc')->get();
		}

		//return response()->json($terms);

		$result = array();
		if (!empty($terms)) {
			foreach ($terms as $term) {
				array_push($result, array(
						"id"  => round($term->id),
						"term_name"  => $term->term_name,
						"term_description" => preg_replace("/^(.{250})([^\.]*\.)(.*)$/", "\\1\\2", $term->term_description),
						"glossary_name" => $term->glossary->glossary_name,
						"value" => $term->term_name,
						"tokens" => array($term->term_name)
					)
				);
			}
		}

		return response()->json($result);
	}

	public function apiShow($id)
	{
		$term = Term::with('glossary')->with('status')->with('objects')->with('owner')->with('properties')->get()->find($id);
		$termArray = $term->toArray();
		$termArray['children'] = array();
		
		if ($term->objects) {
			foreach ($term->objects as $key => $object) {
				$array = array('term_name' => $object->object->term_name, '_children' => null);
				$term2 = Term::with('glossary')->with('status')->with('objects')->with('owner')->with('properties')->get()->find($object->object->id);
				if ($term2->objects) {
					$array['children'] = array();
					foreach ($term2->objects as $key => $object2) {
						$array2 = array('term_name' => $object2->object->term_name, '_children' => null);
						array_push($array['children'],$array2);
					}
				}
				array_push($termArray['children'],$array);
			}
		}
		
		return response()->json($termArray);
	}
}
<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Relation;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;
class RelationController extends Controller
{
	public function index()
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
  		$relations = Relation::orderBy('relation_name', 'asc')->get();
  		return view('relations.index', compact('relations'));
    }

	public function edit(Relation $relation)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		//check if id property exists
		if (!$relation->id) {
			abort(403, 'This Relation no longer exists in the database.');
		}

		return view('relations.edit', compact('relation'));
	}

	public function create(Relation $relation)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		return view('relations.create', compact('relation'));
	}

	public function store(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'relation_name' => 'required|min:3',
			'relation_description' => 'required'
		]);

		Relation::create($request->all());
		return Redirect::route('relations.index')->with('message', 'Relation created');
	}

	public function update(Relation $relation, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'relation_name' => 'required|min:3',
			'relation_description' => 'required'
		]);

		$relation->update($request->all());
		return Redirect::route('relations.show', $relation->slug)->with('message', 'Relation updated.');
	}

	public function destroy(Relation $relation)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		$relation->delete();
		return Redirect::route('relations.index')->with('message', 'Relation deleted.');
	}

	public function apiIndex()
	{
		$relations = Relation::orderBy('id', 'asc')->get();
		return response()->json($relations);
	}

	public function apiShow($id)
	{
		$relation = Relation::find($id);
		return response()->json($relation);
	}
}
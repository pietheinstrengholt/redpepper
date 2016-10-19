<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Term;
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

		return view('terms.index', compact('terms'));
	}

	public function edit(Term $term)
	{
		//check if id property exists
		if (!$term->id) {
			abort(403, 'This term no longer exists in the database.');
		}

		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		return view('terms.edit', compact('term'));
	}

	public function create(Term $term, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		return view('terms.create', compact('term'));
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
			'term_description' => 'required'
		]);
		Term::create($request->all());
		return Redirect::to('/terms')->with('message', 'Term created.');
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
			'term_description' => 'required'
		]);

		$term->update($request->all());
		return Redirect::to('/terms')->with('message', 'Term updated.');
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

		$term->delete();
		return Redirect::to('/terms')->with('message', 'Term deleted.');
	}
}

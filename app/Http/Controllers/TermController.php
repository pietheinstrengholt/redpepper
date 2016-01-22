<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Term;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;
class TermController extends Controller
{
    public function index()
    {
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
  		$terms = Term::orderBy('term_name', 'asc')->get();
  		return view('terms.index', compact('terms'));
    }
	public function edit(Term $term)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		//check if id property exists
		if (!$term->id) {
			abort(403, 'This term no longer exists in the database.');
		}
		return view('terms.edit', compact('term'));
	}
	public function create(Term $term)
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
			'term_definition' => 'required'
		]);
		Term::create($request->all());
		return Redirect::route('terms.index')->with('message', 'Term created');
	}
	public function update(Term $term, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		//validate input form
		$this->validate($request, [
			'term_name' => 'required|min:3',
			'term_definition' => 'required'
		]);
		$term->update($request->all());
		return Redirect::route('terms.show', $term->slug)->with('message', 'Term updated.');
	}
	public function destroy(Term $term)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		$term->delete();
		return Redirect::route('terms.index')->with('message', 'Term deleted.');
	}
}
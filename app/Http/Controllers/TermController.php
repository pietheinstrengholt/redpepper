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
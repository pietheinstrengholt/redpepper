<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Glossary;
use App\Term;
use App\User;
use App\Status;
use Gate;
use Illuminate\Http\Request;
use Redirect;
class GlossaryController extends Controller
{
	public function index()
	{
		$glossaries = Glossary::orderBy('glossary_name', 'asc')->get();
		return view('glossaries.index', compact('glossaries'));
	}

	public function edit(Glossary $glossary)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		//check if id property exists
		if (!$glossary->id) {
			abort(403, 'This glossary no longer exists in the database.');
		}

		$statuses = Status::orderBy('status_name', 'asc')->get();

		return view('glossaries.edit', compact('glossary','statuses'));
	}

	public function create(Glossary $glossary)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		$statuses = Status::orderBy('status_name', 'asc')->get();

		return view('glossaries.create', compact('glossary','statuses'));
	}

	public function show(Glossary $glossary, Request $request)
	{
		//check if id property exists
		if (!$glossary->id) {
			abort(403, 'This glossary no longer exists in the database.');
		}

		$terms = Term::where('glossary_id', $glossary->id)->orderBy('term_name', 'asc')->get();

		//create an array with all first letters from all terms in the database, used for pagination
		$letters = array();
		if (!empty($terms)) {
			foreach ($terms as $term) {
				array_push($letters,substr(strtoupper($term->term_name), 0, 1));
			}
			$letters = array_unique($letters);
		}

		//if letters are not empty check if letter is set with argument, else take first letter from array
		if (!empty($letters)) {
			if ($request->has('letter')) {
				$terms = Term::where('glossary_id', $glossary->id)->orderBy('term_name', 'asc')->where('term_name', 'LIKE', $request->input('letter').'%')->get();
			} else {
				$terms = Term::where('glossary_id', $glossary->id)->orderBy('term_name', 'asc')->where('term_name', 'LIKE', $letters[0].'%')->get();
			}
		}

		return view('glossaries.show', compact('glossary','terms','letters'));
	}


	public function store(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'glossary_name' => 'required|min:3|unique:t_bim_glossaries',
			'glossary_description' => 'required'
		]);

		Glossary::create($request->all());
		return Redirect::route('glossaries.index')->with('message', 'Glossary created');
	}

	public function update(Glossary $glossary, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'glossary_name' => 'required|min:3',
			'glossary_description' => 'required'
		]);

		$glossary->update($request->all());
		return Redirect::route('glossaries.show', $glossary->slug)->with('message', 'Glossary updated.');
	}

	public function destroy(Glossary $glossary)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		$glossary->delete();
		return Redirect::route('glossaries.index')->with('message', 'Glossary deleted.');
	}

	public function apiIndex()
	{
		$glossaries = Glossary::orderBy('glossary_name', 'asc')->get();
		return response()->json($glossaries);
	}

	public function apiShow($id)
	{
		$glossary = Glossary::with('status')->with('terms')->get()->find($id);
		return response()->json($glossary);
	}
}

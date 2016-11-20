<?php
namespace App\Http\Controllers;
use App\Helpers\ActivityLog;
use App\Http\Controllers\Controller;
use App\Term;
use App\User;
use Auth;
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
				$letter = substr(strtoupper($term->term_name), 0, 1);
				if (!ctype_alpha($letter)) {
					$letter = "[0-9]";
				}
				array_push($letters,$letter);
			}
			$letters = array_unique($letters);
		}

		//if letters are not empty check if letter is set with argument, else take first letter from array
		if (!empty($letters)) {
			//check if letter argument is set
			if ($request->has('letter')) {
				//check if the array with letters contains the letter from argument, else use the first one
				if (in_array($request->input('letter'), $letters)) {
					$letter = $request->input('letter');
				} else {
					$letter = $letters[0];
				}
			//no argument is set, use the first letter
			} else {
				$letter = $letters[0];
			}
			if ($letter == "[0-9]") {
				$values = array('1','2','3','4','5','6','7','8','9','0');
				$terms = Term::where(function($q) use ($values) {
					for ($i = 0; $i < count($values); $i++){
			            $q->orwhere('term_name', 'LIKE',  $values[$i] .'%');
			         }
				   })->orderBy('term_name', 'asc')->get();
			} else {
				//get results
				$terms = Term::where('term_name', 'LIKE', $letter.'%')->orderBy('term_name', 'asc')->get();
			}
		} else {
			$terms = collect();
		}

		return view('terms.index', compact('terms', 'letters', 'letter'));
	}

	public function show(Term $term)
	{
		//check if id property exists
		if (!$term->id) {
			abort(403, 'This term no longer exists in the database.');
		}

		return view('terms.show', compact('term'));
	}

	public function search(Request $request)
	{
		//check if id property exists
		$term = Term::where('term_name', 'LIKE', $request->input('search'))->first();

		//check if id property exists
		if (!$term) {
			abort(403, 'No terms with this name exist in the database.');
		}

		return view('terms.search', compact('term'));
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

		//Log activity
		ActivityLog::submit("Term " . $term->term_name . " was updated.");

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

		//Log activity
		ActivityLog::submit("Term " . $term->term_name . " was deleted.");

		$term->delete();
		return Redirect::to('/terms')->with('message', 'Term deleted.');
	}
}

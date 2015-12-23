<?php

namespace App\Http\Controllers;
use DB;
use App\Technical;
use App\TechnicalType;
use App\TechnicalSource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Input;
use Redirect;
use App\User;
use Gate;

class TechnicalSourceController extends Controller
{

    public function index()
    {
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
  		$sources = TechnicalSource::orderBy('source_name', 'asc')->get();
  		return view('sources.index', compact('sources'));
    }

	public function edit(TechnicalSource $source)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		//check if id property exists
		if (!$source->id) {
			abort(403, 'This source no longer exists in the database.');
		}

		return view('sources.edit', compact('source'));
	}

	public function create(TechnicalSource $source)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		return view('sources.create', compact('source'));
	}

	public function store(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'source_name' => 'required|min:3|unique:t_technical_sources',
			'source_description' => 'required'
		]);

		$input = Input::all();
		TechnicalSource::create( $input );
		return Redirect::route('sources.index')->with('message', 'Source created');
	}

	public function update(TechnicalSource $source, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'source_name' => 'required|min:3|unique:t_technical_sources',
			'source_description' => 'required'
		]);

		$input = array_except(Input::all(), '_method');
		$source->update($input);
		return Redirect::route('sources.show', $source->slug)->with('message', 'Source updated.');
	}

	public function destroy(TechnicalSource $source)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		$source->delete();
		return Redirect::route('sources.index')->with('message', 'Source deleted.');
	}

}

<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Technical;
use App\TechnicalDescription;
use App\TechnicalSource;
use App\TechnicalType;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class TechnicalTypeController extends Controller
{
    public function index()
    {
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
  		$types = TechnicalType::orderBy('type_name', 'asc')->get();
  		return view('types.index', compact('types'));
    }

	public function edit(TechnicalType $type)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$type->id) {
			abort(403, 'This type no longer exists in the database.');
		}

		return view('types.edit', compact('type'));
	}

	public function show(TechnicalType $type)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//check if id property exists
		if (!$type->id) {
			abort(403, 'This type no longer exists in the database.');
		}

		$descriptions = TechnicalDescription::where('type_id', $type->id)->orderBy('content', 'asc')->get();

		return view('types.show', compact('type','descriptions'));
	}

	public function create(TechnicalType $type)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		return view('types.create', compact('type'));
	}

	public function store(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'type_name' => 'required|min:3|unique:t_technical_types',
			'type_description' => 'required'
		]);

		TechnicalType::create($request->all());
		return Redirect::route('types.index')->with('message', 'Type created');
	}

	public function update(TechnicalType $type, Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'type_name' => 'required|min:3',
			'type_description' => 'required'
		]);

		$type->update($request->all());
		return Redirect::route('types.show', $type->slug)->with('message', 'Type updated.');
	}

	public function destroy(TechnicalType $type)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		$type->delete();
		return Redirect::route('types.index')->with('message', 'Type deleted.');
	}

}

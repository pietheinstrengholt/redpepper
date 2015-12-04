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

class TechnicalTypeController extends Controller
{

    public function index()
    {
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
		$types = TechnicalType::all();
		return view('types.index', compact('types'));
    }
	
	public function edit(TechnicalType $type)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
		return view('types.edit', compact('type'));
	}
	
	public function create(TechnicalType $type)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
		return view('types.create', compact('type'));
	}	

	public function store()
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
		$input = Input::all();
		TechnicalType::create( $input );
		return Redirect::route('types.index')->with('message', 'Type created');
	}
	 
	public function update(TechnicalType $type)
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }	
		$input = array_except(Input::all(), '_method');
		$type->update($input);
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

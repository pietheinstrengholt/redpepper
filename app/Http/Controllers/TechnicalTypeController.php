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

class TechnicalTypeController extends Controller
{

    public function index()
    {
		$types = TechnicalType::all();
		return view('types.index', compact('types'));
    }
	
	public function edit(TechnicalType $type)
	{
		return view('types.edit', compact('type'));
	}
	
	public function create(TechnicalType $type)
	{
		return view('types.create', compact('type'));
	}	

	public function store()
	{
		$input = Input::all();
		TechnicalType::create( $input );
		return Redirect::route('types.index')->with('message', 'Type created');
	}
	 
	public function update(TechnicalType $type)
	{
		$input = array_except(Input::all(), '_method');
		$type->update($input);
		return Redirect::route('types.show', $type->slug)->with('message', 'Type updated.');
	}
	 
	public function destroy(TechnicalType $type)
	{
		$type->delete();
		return Redirect::route('types.index')->with('message', 'Type deleted.');
	}
	
}

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

class TechnicalSourceController extends Controller
{

    public function index()
    {
		$sources = TechnicalSource::all();
		return view('sources.index', compact('sources'));
    }
	
	public function edit(TechnicalSource $source)
	{
		return view('sources.edit', compact('source'));
	}
	
	public function create(TechnicalSource $source)
	{
		return view('sources.create', compact('source'));
	}	

	public function store()
	{
		$input = Input::all();
		TechnicalSource::create( $input );
		return Redirect::route('sources.index')->with('message', 'Source created');
	}
	 
	public function update(TechnicalSource $source)
	{
		$input = array_except(Input::all(), '_method');
		$source->update($input);
		return Redirect::route('sources.show', $source->slug)->with('message', 'Source updated.');
	}
	 
	public function destroy(TechnicalSource $source)
	{
		$source->delete();
		return Redirect::route('sources.index')->with('message', 'Source deleted.');
	}
	
}

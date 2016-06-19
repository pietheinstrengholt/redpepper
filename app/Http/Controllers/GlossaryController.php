<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Glossary;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;
class GlossaryController extends Controller
{
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
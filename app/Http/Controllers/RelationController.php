<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Relation;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;
class RelationController extends Controller
{
	public function apiIndex()
	{
		$relations = Relation::orderBy('id', 'asc')->get();
		return response()->json($relations);
	}

	public function apiShow($id)
	{
		$relation = Relation::find($id);
		return response()->json($relation);
	}
}
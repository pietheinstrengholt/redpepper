<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Status;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;
class StatusController extends Controller
{
	public function apiIndex()
	{
		$statuses = Status::orderBy('id', 'asc')->get();
		return response()->json($statuses);
	}

	public function apiShow($id)
	{
		$status = Status::find($id);
		return response()->json($status);
	}
}
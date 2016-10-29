<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Activity;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;
class ActivityController extends Controller
{
	public function index()
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		$activities = Activity::with('creator')->orderBy('created_by', 'desc')->paginate(25);
		return view('activities.index', compact('activities'));
	}
}

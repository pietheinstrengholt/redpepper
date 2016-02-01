<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Log;
use App\User;
use Auth;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class LogController extends Controller
{
	public function index()
	{
		//check for superadmin permissions
        if (Gate::denies('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
		
		$logs = Log::orderBy('id', 'desc')->paginate(20);
		return view('logs.index', compact('logs'));
	}
	
	public function show()
	{
		$logs = Log::orderBy('id', 'desc')->whereIn('log_event', ['Changerequest'])->take(10)->get();
		return view('logs.show', compact('logs'));
	}
	
}

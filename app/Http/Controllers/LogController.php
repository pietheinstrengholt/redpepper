<?php

namespace App\Http\Controllers;
use DB;
use App\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Input;
use Redirect;

use Gate;
use App\User;
use Auth;

class LogController extends Controller
{
    public function index(Request $request)
    {
		$logs = Log::orderBy('id', 'desc')->get();
		return view('logs.index', compact('logs'));
    }
}

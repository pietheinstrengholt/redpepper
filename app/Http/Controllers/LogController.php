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
    public function index(Request $request)
    {
		$logs = Log::orderBy('id', 'desc')->paginate(20);
		return view('logs.index', compact('logs'));
    }
}

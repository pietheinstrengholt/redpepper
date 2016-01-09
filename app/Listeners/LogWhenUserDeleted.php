<?php

namespace App\Listeners;

use App\Events\UserDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\User;
use Auth;

class LogWhenUserDeleted
{
	public function __construct()
	{
		//
	}

	public function handle(UserDeleted $event)
	{
		\Log::info("USER DELETED {$event->user->username}"); 
		
		$log = new Log;
		$log->log_event = 'User';
		$log->action = 'Deleted';
		$log->username_id = $event->user->id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

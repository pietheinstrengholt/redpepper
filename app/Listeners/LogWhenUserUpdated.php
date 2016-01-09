<?php

namespace App\Listeners;

use App\Events\UserUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\User;
use Auth;

class LogWhenUserUpdated
{
	public function __construct()
	{
		//
	}

	public function handle(UserUpdated $event)
	{
		\Log::info("USER UPDATED {$event->user->username}"); 
		
		$log = new Log;
		$log->log_event = 'User';
		$log->action = 'Updated';
		$log->username_id = $event->user->id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

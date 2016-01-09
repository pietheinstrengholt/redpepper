<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\User;
use Auth;

class LogWhenUserCreated
{
	public function __construct()
	{
		//
	}

	public function handle(UserCreated $event)
	{
		\Log::info("USER CREATED {$event->user->username}"); 
		
		$log = new Log;
		$log->log_event = 'User';
		$log->action = 'Created';
		$log->username_id = $event->user->id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

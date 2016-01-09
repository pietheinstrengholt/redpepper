<?php

namespace App\Listeners;

use App\Events\ChangeRequestDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\ChangeRequest;
use Auth;

class LogWhenChangeRequestDeleted
{
	public function __construct()
	{
		//
	}

	public function handle(ChangeRequestDeleted $event)
	{
		\Log::info("CHANGEREQUEST DELETED {$event->changerequest->id}"); 
		
		$log = new Log;
		$log->log_event = 'Changerequest';
		$log->action = 'Deleted';
		$log->changerequest_id = $event->changerequest->id;
		$log->template_id = $event->changerequest->template_id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

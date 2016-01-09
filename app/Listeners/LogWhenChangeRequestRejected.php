<?php

namespace App\Listeners;

use App\Events\ChangeRequestRejected;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\ChangeRequest;
use Auth;

class LogWhenChangeRequestRejected
{
	public function __construct()
	{
		//
	}

	public function handle(ChangeRequestRejected $event)
	{
		\Log::info("CHANGEREQUEST REJECTED {$event->changerequest->id}"); 
		
		$log = new Log;
		$log->log_event = 'Changerequest';
		$log->action = 'Rejected';
		$log->changerequest_id = $event->changerequest->id;
		$log->template_id = $event->changerequest->template_id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

<?php

namespace App\Listeners;

use App\Events\ChangeRequestApproved;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\ChangeRequest;
use Auth;

class LogWhenChangeRequestApproved
{
	public function __construct()
	{
		//
	}

	public function handle(ChangeRequestApproved $event)
	{
		\Log::info("CHANGEREQUEST APPROVED {$event->changerequest->id}"); 
		
		$log = new Log;
		$log->log_event = 'Changerequest';
		$log->action = 'Approved';
		$log->changerequest_id = $event->changerequest->id;
		$log->template_id = $event->changerequest->template_id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

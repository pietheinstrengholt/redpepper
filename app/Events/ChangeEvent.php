<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Log;
use App\User;
use Mail;
use App\ChangeRequest;
use App\Template;

class ChangeEvent extends Event
{
	use SerializesModels;

	/**
	* Create a new event instance.
	*
	* @return void
	*/
	public function __construct($event)
	{
		$log = new Log;
		$log->log_event = $event['log_event'];
		$log->action = $event['action'];
		
		if (!empty($event['changerequest_id'])) {
			$log->changerequest_id = $event['changerequest_id'];
		}
		
		if (!empty($event['section_id'])) {
			$log->section_id = $event['section_id'];
		}

		if (!empty($event['template_id'])) {
			$log->template_id = $event['template_id'];
		}

		if (!empty($event['username_id'])) {
			$log->username_id = $event['username_id'];
		}		

		$log->created_by = $event['created_by'];
		$log->save();
		
		$user = User::findOrFail($event['created_by']);
		
		$event['username'] = $user->username;
		
		if ($event['log_event'] == "ChangeRequest") {
			
			$changerequest = ChangeRequest::findOrFail($event['changerequest_id']);
			$template = Template::findOrFail($changerequest->template_id);
			$event['template_name'] = $template->template_name;
			
			Mail::send('emails.changerequest', $event, function($message)
			{
				$message->from(env('MAIL_USERNAME'));
				$message->to(env('MAIL_TO'), env('MAIL_NAME'));
				$message->subject('RADAR notification');
			});
			
		}
		
		if ($event['log_event'] == "Template Excel" && $user->role == "builder") {
			Mail::send('emails.builderexcel', $event, function($message)
			{
				$message->from(env('MAIL_USERNAME'));
				$message->to(env('MAIL_TO'), env('MAIL_NAME'));
				$message->subject('RADAR notification');
			});
		}
		
	}

	/**
	* Get the channels the event should be broadcast on.
	*
	* @return array
	*/
	public function broadcastOn()
	{
	return [];
	}
}

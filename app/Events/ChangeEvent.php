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
		$log->content_type = $event['content_type'];
		$log->content_action = $event['content_action'];
		$log->content_name = $event['content_name'];
		$log->created_by = $event['created_by'];
		$log->save();
		
		$user = User::findOrFail($event['created_by']);
		
		$event['username'] = $user->username;
		
		if ($event['content_type'] == "ChangeRequest") {
			
			$changerequest = ChangeRequest::findOrFail($event['content_name']);
			$template = Template::findOrFail($changerequest->template_id);
			$event['template_name'] = $template->template_name;
			
			Mail::send('emails.changerequest', $event, function($message)
			{
				$message->from(env('MAIL_USERNAME'));
				$message->to(env('MAIL_TO'), env('MAIL_NAME'));
				$message->subject('RADAR notification');
			});
			
		}
		
		if ($event['content_type'] == "Template Excel" && $user->role == "builder") {
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

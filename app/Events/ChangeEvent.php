<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Log;

use Mail;

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
		
		if ($event['content_type'] == "ChangeRequest") {
			
			Mail::send('emails.notification', $event, function($message)
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

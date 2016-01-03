<?php

namespace App\Listeners;

use App\Events\ChangeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Log;
use App\User;
use Mail;
use App\ChangeRequest;
use App\Template;

class ChangeEventListener
{
	public function __construct()
	{
		//
	}

	public function handle(ChangeEvent $changeevent)
	{
		
		$changeevent = get_object_vars($changeevent);
		
		$log = new Log;
		$log->log_event = $changeevent['event']['log_event'];
		$log->action = $changeevent['event']['action'];

		if (!empty($changeevent['event']['changerequest_id'])) {
			$log->changerequest_id = $changeevent['event']['changerequest_id'];
		}

		if (!empty($changeevent['event']['section_id'])) {
			$log->section_id = $changeevent['event']['section_id'];
		}

		if (!empty($changeevent['event']['template_id'])) {
			$log->template_id = $changeevent['event']['template_id'];
		}

		if (!empty($changeevent['event']['username_id'])) {
			$log->username_id = $changeevent['event']['username_id'];
		}

		$log->created_by = $changeevent['event']['created_by'];
		$log->save();

		$user = User::findOrFail($changeevent['event']['created_by']);

		$changeevent['event']['username'] = $user->username;

		if ($changeevent['event']['log_event'] == "ChangeRequest") {

			$changerequest = ChangeRequest::findOrFail($changeevent['event']['changerequest_id']);
			$template = Template::findOrFail($changerequest->template_id);
			$changeevent['event']['template_name'] = $template->template_name;

			Mail::send('emails.changerequest', $changeevent, function($message)
			{
				$message->from(env('MAIL_USERNAME'));
				$message->to(env('MAIL_TO'), env('MAIL_NAME'));
				$message->subject('RADAR notification');
			});
		}

		if ($changeevent['event']['log_event'] == "Template Excel" && $user->role == "builder") {
			Mail::send('emails.builderexcel', $changeevent, function($message)
			{
				$message->from(env('MAIL_USERNAME'));
				$message->to(env('MAIL_TO'), env('MAIL_NAME'));
				$message->subject('RADAR notification');
			});
		}
	}
}

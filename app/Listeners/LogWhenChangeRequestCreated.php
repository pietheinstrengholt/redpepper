<?php

namespace App\Listeners;

use App\Events\ChangeRequestCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\ChangeRequest;
use Auth;
use Mail;
use App\User;
use App\Template;
use App\Helper;

class LogWhenChangeRequestCreated
{
	public function __construct()
	{
		//
	}

	public function handle(ChangeRequestCreated $event)
	{
		\Log::info("CHANGEREQUEST CREATED {$event->changerequest->id}"); 
		
		$log = new Log;
		$log->log_event = 'Changerequest';
		$log->action = 'Created';
		$log->changerequest_id = $event->changerequest->id;
		$log->template_id = $event->changerequest->template_id;
		$log->created_by = Auth::user()->id;
		$log->save();

		$array = array();
		
		$array['changerequest_id'] = $event->changerequest->id;
		$user = User::findOrFail(Auth::user()->id);
		$array['username'] = $user->username;
		$template = Template::findOrFail($event->changerequest->template_id);
		$array['template_name'] = $template->template_name;
		
		if (!(Helper::setting('superadmin_process_directly') == "yes" && Auth::user()->role == "superadmin")) {
		
			Mail::send('emails.changerequest', $array, function($message)
			{
				$message->from(env('MAIL_USERNAME'));
				$message->to(env('MAIL_TO'), env('MAIL_NAME'));
				$message->subject('RADAR notification');
			});
		}
		
	}
}

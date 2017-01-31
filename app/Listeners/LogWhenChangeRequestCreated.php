<?php

namespace App\Listeners;

use App\Events\ChangeRequestCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\ChangeRequest;
use Auth;
use Mail;
use App\User;
use App\Template;
use App\Helpers\Settings;

class LogWhenChangeRequestCreated
{
	public function __construct()
	{
		//
	}

	public function handle(ChangeRequestCreated $event)
	{
		$array = array();

		$array['changerequest_id'] = $event->changerequest->id;
		$user = User::findOrFail(Auth::user()->id);
		$array['username'] = $user->username;
		$template = Template::findOrFail($event->changerequest->template_id);
		$array['template_name'] = $template->template_name;

		if (!(Settings::get('superadmin_process_directly') == "yes" && Auth::user()->role == "superadmin")) {

			//email the system administrator
			if (!empty(Settings::get('administrator_email'))) {
				Mail::send('emails.changerequest', $array, function($message)
				{
					$message->from(Auth::user()->email);
					$message->to(Settings::get('administrator_email'));
					$message->subject('Notification from the ' . Settings::get('tool_name'));
				});
			}

			//email the approver if set
			if ($event->changerequest->approver) {
				$approver = User::findOrFail($event->changerequest->approver);
				Mail::send('emails.changerequest', $array, function($message) use ($approver)
				{
					$message->from(Auth::user()->email);
					$message->to($approver->email);
					$message->subject('Notification from the ' . Settings::get('tool_name'));
				});
			}
		}
	}
}

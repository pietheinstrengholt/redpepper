<?php

namespace App\Listeners;

use App\Events\TemplateCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\Template;
use Auth;
use App\User;
use Mail;
use App\Helper;

class LogWhenTemplateCreated
{
	public function __construct()
	{
		//
	}

	public function handle(TemplateCreated $event)
	{
		\Log::info("TEMPLATE CREATED {$event->template->template_name}"); 
		
		$log = new Log;
		$log->log_event = 'Template';
		$log->action = 'Created';
		$log->section_id = $event->template->section_id;
		$log->template_id = $event->template->id;
		$log->created_by = Auth::user()->id;
		$log->save();

		$user = User::findOrFail(Auth::user()->id);
		$array['username'] = $user->username;
		$template = Template::findOrFail($event->template->id);
		$array['template_name'] = $template->template_name;
		$array['template_id'] = $event->template->id;
		$array['section_id'] = $event->template->section_id;
		
		Mail::send('emails.template', $array, function($message)
		{
			$message->from(env('MAIL_USERNAME'));
			$message->to(Helper::setting('administrator_email'));
			$message->subject('Notification from the ' . Helper::setting('tool_name'));
		});
		
	}
}

<?php

namespace App\Listeners;

use App\Events\TemplateUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\Template;
use Auth;

class LogWhenTemplateUpdated
{
	public function __construct()
	{
		//
	}

	public function handle(TemplateUpdated $event)
	{
		\Log::info("TEMPLATE UPDATED {$event->template->template_name}"); 
		
		$log = new Log;
		$log->log_event = 'Template';
		$log->action = 'Updated';
		$log->section_id = $event->template->section_id;
		$log->template_id = $event->template->id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

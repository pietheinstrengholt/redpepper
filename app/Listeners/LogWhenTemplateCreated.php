<?php

namespace App\Listeners;

use App\Events\TemplateCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\Template;
use Auth;

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
	}
}

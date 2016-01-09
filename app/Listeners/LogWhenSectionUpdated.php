<?php

namespace App\Listeners;

use App\Events\SectionUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\Section;
use Auth;

class LogWhenSectionUpdated
{
	public function __construct()
	{
		//
	}

	public function handle(SectionUpdated $event)
	{
		\Log::info("SECTION UPDATED {$event->section->section_name}"); 
		
		$log = new Log;
		$log->log_event = 'Section';
		$log->action = 'Updated';
		$log->section_id = $event->section->id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

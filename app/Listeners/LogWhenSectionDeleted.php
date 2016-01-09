<?php

namespace App\Listeners;

use App\Events\SectionDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\Section;
use Auth;

class LogWhenSectionDeleted
{
	public function __construct()
	{
		//
	}

	public function handle(SectionDeleted $event)
	{
		\Log::info("SECTION DELETED {$event->section->section_name}"); 
		
		$log = new Log;
		$log->log_event = 'Section';
		$log->action = 'Deleted';
		$log->section_id = $event->section->id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

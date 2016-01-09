<?php

namespace App\Listeners;

use App\Events\SectionCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Log;
use App\Section;
use Auth;

class LogWhenSectionCreated
{
	public function __construct()
	{
		//
	}

	public function handle(SectionCreated $event)
	{
		\Log::info("SECTION CREATED {$event->section->section_name}"); 
		
		$log = new Log;
		$log->log_event = 'Section';
		$log->action = 'Created';
		$log->section_id = $event->section->id;
		$log->created_by = Auth::user()->id;
		$log->save();
	}
}

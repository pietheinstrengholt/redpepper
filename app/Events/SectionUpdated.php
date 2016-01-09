<?php

namespace App\Events;

use App\Section;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class SectionUpdated extends Event
{
	use SerializesModels;

	public $section;
	
	public function __construct(Section $section)
	{
		$this->section = $section;
	}
}

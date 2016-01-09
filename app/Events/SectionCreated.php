<?php

namespace App\Events;

use App\Section;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class SectionCreated extends Event
{
	use SerializesModels;

	public $section;
	
	public function __construct(Section $section)
	{
		$this->section = $section;
	}
}

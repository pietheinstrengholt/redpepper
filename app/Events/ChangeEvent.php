<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ChangeEvent extends Event
{
	use SerializesModels;

	public $event;
	
	public function __construct($event)
	{
		$this->event = $event;
	}
}

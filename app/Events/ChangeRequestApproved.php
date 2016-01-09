<?php

namespace App\Events;

use App\ChangeRequest;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ChangeRequestApproved extends Event
{
	use SerializesModels;

	public $changerequest;
	
	public function __construct(ChangeRequest $changerequest)
	{
		$this->changerequest = $changerequest;
	}
}

<?php

namespace App\Events;

use App\Template;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class TemplateUpdated extends Event
{
	use SerializesModels;

	public $template;
	
	public function __construct(Template $template)
	{
		$this->template = $template;
	}
}

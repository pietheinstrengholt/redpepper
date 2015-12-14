<?php

namespace App\Handlers\Events;

use App\Events\ChangeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangeEventHandler
{
  /**
   * Create the event handler.
   *
   * @return void
   */
  public function __construct()
  {
      //
  }

  /**
   * Handle the event.
   *
   * @param  ChangeEvent  $event
   * @return void
   */
  public function handle(ChangeEvent $event)
  {
      //
  }
}

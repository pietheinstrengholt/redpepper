<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Log;

class ChangeEvent extends Event
{
  use SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($content, $description, $created_by)
  {
    $log = new Log;
    $log->content = $content;
    $log->description = $description;
    $log->created_by = $created_by;
    $log->save();
  }

  /**
   * Get the channels the event should be broadcast on.
   *
   * @return array
   */
  public function broadcastOn()
  {
    return [];
  }
}

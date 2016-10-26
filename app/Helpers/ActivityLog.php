<?php

namespace app\Helpers;
use Auth;
use App\Helpers\ActivityLog;
use App\Activity;

class ActivityLog
{
	public static function submit($event)
	{
		if (!empty($event)) {
			$activity = new Activity;
			$activity->description = $event;
			if (Auth::check()) {
				$activity->created_by = Auth::user()->id;
			}
			$activity->save();
		}
	}
}

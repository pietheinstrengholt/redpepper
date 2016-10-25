<?php 

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Auth;
use App\Subject;
use Gate;
use App\User;

class SubjectComposer {
	public function compose(View $view) {
		//only non guests will see the hidden templates
		if (Auth::guest()) {
			$view->with('subjects', Subject::where('visible', '<>' , 'False')->where('parent_id', null)->orWhere(function ($query) {
				$query->where('visible', '<>' , 'False')->where('parent_id', 0);
			})->orderBy('subject_order', 'asc')->orderBy('subject_name', 'asc')->get());
		} else {
			$view->with('subjects', Subject::where('parent_id', null)->orWhere(function ($query) {
				$query->where('parent_id', 0);
			})->orderBy('subject_order', 'asc')->orderBy('subject_name', 'asc')->get());
		}
	}
}

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
			$view->with('subjects', Subject::orderBy('subject_name', 'asc')->where('visible', '<>' , 'False')->get());
		} else {
			$view->with('subjects', Subject::orderBy('subject_name', 'asc')->get());
		}
	}
}

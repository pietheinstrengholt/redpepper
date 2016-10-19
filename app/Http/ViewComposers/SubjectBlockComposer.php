<?php 

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Auth;
use App\Subject;
use Gate;
use App\User;

class SubjectBlockComposer {
	public function compose(View $view) {
		//only non guests will see the hidden templates
		if (Gate::allows('see-nonvisible-content')) {
			$view->with('subjects', Subject::orderBy('subject_order', 'asc')->orderBy('subject_name', 'asc')->where('parent_id', null)->get());
		} else {
			$view->with('subjects', Subject::orderBy('subject_order', 'asc')->orderBy('subject_name', 'asc')->where('visible', '<>' , 'False')->where('parent_id', null)->get());
		}
	}
}

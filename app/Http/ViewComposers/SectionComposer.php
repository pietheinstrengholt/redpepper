<?php 

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Auth;
use App\Section;
use Gate;
use App\User;

class SectionComposer {
	public function compose(View $view) {
		//only non guests will see the hidden templates
		if (Auth::guest()) {
			$view->with('sections', Section::orderBy('section_name', 'asc')->where('visible', '<>' , 'False')->get());
		} else {
			$view->with('sections', Section::orderBy('section_name', 'asc')->get());
		}
	}
}

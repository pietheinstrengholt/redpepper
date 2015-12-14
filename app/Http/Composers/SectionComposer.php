<?php namespace App\Http\Composers;

use Illuminate\Contracts\View\View;
use DB;
use App\Section;
use Gate;
use App\User;

class SectionComposer {
  public function compose(View $view)
  {
  	if (Gate::denies('superadmin')) {
  		$view->with('sections', Section::orderBy('section_name', 'asc')->where('visible', 'True')->get());
  	} else {
  		$view->with('sections', Section::orderBy('section_name', 'asc')->get());
  	}
  }
}

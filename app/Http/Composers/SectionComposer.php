<?php namespace App\Http\Composers;

use Illuminate\Contracts\View\View;
use DB;
use App\Section;

class SectionComposer {

    public function compose(View $view)
    {
        $view->with('sections', Section::all());
    }

}
<?php

namespace App;
use App\User;
use App\Section;
use Auth;
use Gate;

class AuthService {

	public function getSectionsList()
	{
		//this function is needed to return all the sections where the user has rights on. Auth::user()->sections, will only return the sections that have been added manually.
		$sectionRights = array();

		$sections = Section::orderBy('section_name', 'asc')->get();

		foreach ($sections as $section) {
			if (Auth::user()->can('update-section', $section)) {
				array_push($sectionRights,$section->id);
			}
		}

		//abort if sectionRights array is empty
		if (empty($sectionRights)) {
			abort(403, 'Unauthorized action. You don\'t have access to any sections.');
		}

		//return Array with sections
		return $sectionRights;
	}
}

?>

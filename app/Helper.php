<?php

namespace App;
use App\User;
use App\Setting;
use App\Term;

class Helper {

	//function to make hyperlinks from urls
	public static function formatUrlsInText($text) {
		return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a target="_blank" href="$1">$1</a>', $text);
	}

	public static function returnHistory($object) {
		if ($object['updated_at'] > $object['created_at']) {
			$lastDate = $object['updated_at'];
		} else {
			$lastDate = $object['created_at'];
		}
		
		$user = User::find($object['created_by']);
		
		if (!empty($user)) {
			return "Last updated at " . date('d F Y', strtotime($lastDate)) . " by " . $user->firstname . " " . $user->lastname;
		} else {
			return "Last updated at " . date('d F Y', strtotime($lastDate));			
		}
	}
	
	public static function setting($input) {
		$setting = Setting::where('config_key', $input)->first();
		if (!empty($setting)) {
			return $setting->config_value;
		}
	}
	
	public static function addTermLinks($text) {
		//retrieve words from database
		$words = Term::all();
		
		if (!empty($words)) {
			//build dictionary with values that needs replacement
			$patterns = array();
			foreach ($words as $word) {
				$patterns[$word->id] = $word->term_name;
			}

			//build dictionary with values the replacements
			$replacements = array();
			foreach ($words as $word) {
				$replacements[$word->id] = "<a href=\"" . url('terms') . "/" . $word->id . "\">" . $patterns[$word->id] . "</a>";
			}

			//return text, replace words from dictionary with hyperlinks
			return str_replace($patterns, $replacements, $text);			
		} else {
			return $text;
		}


	}
	
	public static function contentAdjust($input) {
		$output = self::formatUrlsInText($input);
		$output = self::addTermLinks($output);
		return $output;
	}
	
	public static function highlightInput($input1, $input2) {
		return str_ireplace($input1, "<strong>$input1</strong>", $input2);
	}
	
}

?>
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
		if ($input == 'homescreen_image' && empty($setting)) {
			return 'default.jpg';
		}
		if ($input == 'css_style' && empty($setting)) {
			return 'bootstrap.min.css';
		}
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

	public static function returnSearch($query, $str, $wordcount) {
		$explode = explode($query, $str);
		$result = null;
		//if explode count is one the query was not found
		if (count($explode) == 1) {
			$result = implode(' ', array_slice(str_word_count($explode[0], 2), -$wordcount, $wordcount)) . " ";
		}
		//if explode count is more than one the query was found at least one time
		if (count($explode) > 1) {
			//check for if the string begins with the query
			if (!empty($explode[0])) {
				$result =  "..." . implode(' ', array_slice(str_word_count($explode[0], 2), -$wordcount, $wordcount)) . " ";
			}

			$result = $result . $query;

			if (!empty($explode[1])) {
				$result = $result . " " . implode(' ', array_slice(str_word_count($explode[1], 2), 0, $wordcount)) . "...";
			}
		}
		//return result
		return $result;
	}

}

?>

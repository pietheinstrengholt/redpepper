<?php

namespace App;
use App\User;
use App\Setting;
use App\Term;

//Todo: move Helper file to Helpers folder
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

		//build dictionary with values the replacements
		$array_of_words = array();
		if (!empty($words)) {
			foreach ($words as $word) {
				array_push($array_of_words,$word->term_name);
			}
		}

		//use preg_replace_callback to not loose case
		$pattern = '#(?<=^|\W)('. implode('|', array_map('preg_quote', $array_of_words)) . ')(?=$|\W)#i';
		$callback = function ($match) {
		    return "<a class='bim-link' href=" . url('searchterms') . "?search=" . urlencode($match[0]) . ">" . $match[0] . "</a>";
		};
		return preg_replace_callback($pattern, $callback, $text);
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

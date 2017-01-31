<?php

namespace app\Helpers;
use App\Setting;

class Settings
{
	public static function get($input) {
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
}

?>

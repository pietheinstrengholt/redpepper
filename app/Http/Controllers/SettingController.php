<?php

namespace App\Http\Controllers;
use App\Setting;
use App\Http\Controllers\Controller;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Redirect;

class SettingController extends Controller
{

    public function index()
    {
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}
		
		//rotate array in order to process it better
		$config_array = array();

  		$settings = Setting::orderBy('config_key', 'asc')->get();
		
		if (!empty($settings)) {
			foreach($settings as $setting) {
				$config_key = $setting['config_key'];
				$config_array[$config_key] = $setting['config_value'];
			}
		}
		
		//scan images from background folder to show them as a selectable dropdown list in the settings
		$scanned_img_directory = array_diff(scandir(base_path() . '/public/img/background/'), array('..', '.'));
		$scanned_img_directory = array_combine($scanned_img_directory, $scanned_img_directory);
		
		//scan css from bootstrap folder to show them as a selectable dropdown list in the settings
		$scanned_css_directory = array_diff(scandir(base_path() . '/public/css/'), array('..', '.'));
		
		$scanned_css_filter = array();
		foreach ($scanned_css_directory as $cssfilename) {
			if ((substr($cssfilename, -4) == '.css') && (substr($cssfilename, 0, 9) == 'bootstrap')) {
				array_push($scanned_css_filter,$cssfilename);
			}
		}
		
		$scanned_css_directory = array_combine($scanned_css_filter, $scanned_css_filter);
		
  		return view('settings.index', compact('config_array','scanned_img_directory','scanned_css_directory'));
    }

	public function store(Request $request)
	{
		//check for superadmin permissions
		if (Gate::denies('superadmin')) {
			abort(403, 'Unauthorized action.');
		}

		//validate input form
		$this->validate($request, [
			'bank_name' => 'required|min:3',
			'fieldname_property1' => 'required',
			'fieldname_property2' => 'required',
			'main_message1' => 'required',
			'main_message2' => 'required',
			'tool_name' => 'required',
			'administrator_email' => 'required|email',
			'superadmin_process_directly' => 'required',
		]);
		
		//truncate table
		Setting::truncate();

		$setting = new Setting;
		$setting->config_key = 'bank_name';
		$setting->config_value = $request->input('bank_name');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'fieldname_property1';
		$setting->config_value = $request->input('fieldname_property1');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'fieldname_property2';
		$setting->config_value = $request->input('fieldname_property2');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'main_message1';
		$setting->config_value = $request->input('main_message1');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'main_message2';
		$setting->config_value = $request->input('main_message2');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'tool_name';
		$setting->config_value = $request->input('tool_name');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'administrator_email';
		$setting->config_value = $request->input('administrator_email');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'superadmin_process_directly';
		$setting->config_value = $request->input('superadmin_process_directly');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'homescreen_image';
		$setting->config_value = $request->input('homescreen_image');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'css_style';
		$setting->config_value = $request->input('css_style');
		$setting->save();
		
		return Redirect::to('/')->withErrors(['Settings updated']);
	}
}

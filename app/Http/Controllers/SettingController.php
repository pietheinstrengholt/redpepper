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
		
  		return view('settings.index', compact('config_array'));
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
			'administrator_email' => 'required',
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
		$setting->config_key = 'administrator_email';
		$setting->config_value = $request->input('administrator_email');
		$setting->save();
		
		$setting = new Setting;
		$setting->config_key = 'superadmin_process_directly';
		$setting->config_value = $request->input('superadmin_process_directly');
		$setting->save();
		
		return Redirect::to('/');
	}
}

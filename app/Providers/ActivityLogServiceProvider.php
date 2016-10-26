<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\ActivityLog;

class ActivityLogServiceProvider extends ServiceProvider
{
	protected $defer = true;

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
	    //
	}
	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
	    //$this->app->bind('ActivityLog', function(){
	        //return new \App\Helpers\ActivityLog;
	    //});
	    foreach (glob(app_path().'/Helpers/*.php') as $filename){
            require_once($filename);
        }
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
	    //return ['App\Helpers\ActivityLog'];
	}

}

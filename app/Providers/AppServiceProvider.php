<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
		//show drop-down in nav menu
		view()->composer('layouts.master', 'App\Http\ViewComposers\SubjectComposer');
		view()->composer('index', 'App\Http\ViewComposers\SubjectComposer');
	}

	/**
	* Register any application services.
	*
	* @return void
	*/
	public function register() {
		//
	}
}

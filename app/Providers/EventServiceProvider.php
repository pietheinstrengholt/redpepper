<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [	
        'App\Events\SectionCreated' => [
            'App\Listeners\LogWhenSectionCreated',
        ],

        'App\Events\SectionUpdated' => [
            'App\Listeners\LogWhenSectionUpdated',
        ],
		
        'App\Events\SectionDeleted' => [
            'App\Listeners\LogWhenSectionDeleted',
        ],
		
        'App\Events\TemplateCreated' => [
            'App\Listeners\LogWhenTemplateCreated',
        ],

        'App\Events\TemplateUpdated' => [
            'App\Listeners\LogWhenTemplateUpdated',
        ],
		
        'App\Events\TemplateDeleted' => [
            'App\Listeners\LogWhenTemplateDeleted',
        ],

        'App\Events\UserCreated' => [
            'App\Listeners\LogWhenUserCreated',
        ],

        'App\Events\UserUpdated' => [
            'App\Listeners\LogWhenUserUpdated',
        ],
		
        'App\Events\UserDeleted' => [
            'App\Listeners\LogWhenUserDeleted',
        ],
		
        'App\Events\ChangeRequestCreated' => [
            'App\Listeners\LogWhenChangeRequestCreated',
        ],

        'App\Events\ChangeRequestApproved' => [
            'App\Listeners\LogWhenChangeRequestApproved',
        ],
		
        'App\Events\ChangeRequestRejected' => [
            'App\Listeners\LogWhenChangeRequestRejected',
        ],
		
        'App\Events\ChangeRequestDeleted' => [
            'App\Listeners\LogWhenChangeRequestDeleted',
        ],
		
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}

<?php
    protected $listen = [
        \App\Events\UserRegistered::class => [
            \App\Listeners\SendWelcomeMail::class,
        ],
    ];

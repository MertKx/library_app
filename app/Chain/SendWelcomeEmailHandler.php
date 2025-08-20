<?php
namespace App\Chain;

use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class SendWelcomeEmailHandler
{
    /**
     * Handle the payload and return it for the next handler
     */
    public function handle(array $payload): array
    {
        if (isset($payload['user'])) {
            // Send welcome email
            Mail::to($payload['user']->email)->send(new WelcomeMail($payload['user']));
        }

        // Return payload for next handler in the pipeline
        return $payload;
    }
}

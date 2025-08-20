<?php
namespace App\Chain;

class LogRegistrationHandler
{
    /**
     * Handle the payload and return it for the next handler
     */
    public function handle(array $payload): array
    {
        if (isset($payload['user'])) {
            // Log new user info
            \Log::info("New user: {$payload['user']->email}");
        }

        // Return payload for next handler in the pipeline
        return $payload;
    }
}

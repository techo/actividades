<?php

namespace App\Services\SocialAuth;

use Google_Client;

class GoogleProvider implements SocialProviderInterface
{
    public function validate(string $token): ?array
    {
        $client = new Google_Client([
            'client_id' => config('services.google.client_id'),
        ]);

        $payload = $client->verifyIdToken($token);

        if (!$payload) {
            return null;
        }

        return [
            'provider'        => 'google',
            'social_id'       => $payload['sub'],
            'email'           => $payload['email'] ?? null,
            'email_verified'  => $payload['email_verified'] ?? false,
        ];
    }
}

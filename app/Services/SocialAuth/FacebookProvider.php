<?php

namespace App\Services\SocialAuth;

class FacebookProvider implements SocialProviderInterface
{
    public function validate(string $token): ?array
    {
        $url = 'https://graph.facebook.com/me'
             . '?fields=id,email'
             . '&access_token=' . $token;

        $response = @file_get_contents($url);
        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);

        if (!isset($data['id'])) {
            return null;
        }

        return [
            'provider'        => 'facebook',
            'social_id'       => $data['id'],
            'email'           => $data['email'] ?? null,
            'email_verified'  => true, // Facebook solo devuelve email válido
        ];
    }
}

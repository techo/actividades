<?php

namespace App\Services\SocialAuth;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Log;

class AppleProvider implements SocialProviderInterface
{
    public function validate(string $token): ?array
    {
        try {
            $keys = json_decode(
                file_get_contents('https://appleid.apple.com/auth/keys'),
                true
            );

            $decoded = JWT::decode(
                $token,
                JWK::parseKeySet($keys),
                ['RS256']
            );

            if ($decoded->aud !== config('services.apple.client_id')) {
                return null;
            }

            return [
                'provider'        => 'apple',
                'social_id'       => $decoded->sub,
                'email'           => $decoded->email ?? null,
                'email_verified'  => filter_var(
                    $decoded->email_verified ?? false,
                    FILTER_VALIDATE_BOOLEAN
                ),
            ];
        } catch (\Exception $e) {
            Log::error('Apple token invalid', [
                'error' => $e->getMessage(),
            ]);
            return  null;
        }
    }
}

<?php

namespace App\Services\SocialAuth;

use InvalidArgumentException;

class SocialProviderFactory
{
    public static function make(string $provider): SocialProviderInterface
    {
        switch ($provider) {
            case 'google':
                return new GoogleProvider();
            case 'facebook':
                return new FacebookProvider();
            case 'apple':
                return new AppleProvider();
            default:
                throw new InvalidArgumentException('Proveedor no soportado');
        }
    }
}

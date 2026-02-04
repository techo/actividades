<?php

namespace App\Services\SocialAuth;

interface SocialProviderInterface
{
    /**
     * Valida el token contra el proveedor
     * y devuelve datos confiables
     */
    public function validate(string $token): ?array;
}

<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PassExiste implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function passes($attribute, $value)
    {
        return Hash::check($value, Auth::user()->password);
    }

    public function message() {
        return 'Ingrese su contraseña actual';
    }
}

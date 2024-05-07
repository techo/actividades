<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearHomeHeader extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->idPais == auth()->user()->idPaisPermitido;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'header' => 'required',
            'imagen' => 'nullable|file|image|dimensions:max_width=380,max_height=248',
        ];
    }
}

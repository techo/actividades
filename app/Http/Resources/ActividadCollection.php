<?php

namespace App\Http\Resources;

use App\Actividad;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActividadCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection
        ];
    }
}

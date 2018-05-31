<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Exports\MisActividadesExport;

class CoordinadorActividadesController extends BaseController
{
    public function index(Request $request)
    {
        $export = new MisActividadesExport($request->filter, $request->sort);
        $collection = $export->collection();
        $result = $this->paginate($collection, 10);
        return $result;
    }
}

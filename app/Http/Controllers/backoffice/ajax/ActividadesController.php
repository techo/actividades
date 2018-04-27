<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Exports\ActividadesExport;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class ActividadesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $export = new ActividadesExport($request->filter, $request->sort);
        $collection = $export->collection();
        $result = $this->paginate($collection, 10);
        return $result;
    }

}

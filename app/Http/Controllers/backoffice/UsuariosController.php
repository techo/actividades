<?php

namespace App\Http\Controllers\backoffice;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsuariosController extends Controller
{
    public function index()
    {
        $datatableConfig = config('datatables.usuarios');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.usuarios.index', compact('fields', 'sortOrder'));
    }
}

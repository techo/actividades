<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Oficina;
use Illuminate\Http\Request;

class OficinasController extends Controller
{
    public function index()
    {
        return Oficina::all();
    }
}

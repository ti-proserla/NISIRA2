<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\NPrimaryKey;


class LicenciasController extends Controller
{
    public function index(){
        return response()->json(NPrimaryKey::all());
    }
}

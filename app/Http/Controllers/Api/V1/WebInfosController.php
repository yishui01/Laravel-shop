<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\WebInfo;
use Illuminate\Http\Request;

class WebInfosController extends Controller
{
    public function index()
    {
        return $this->response->array(WebInfo::all())->setStatusCode(201);
    }
}

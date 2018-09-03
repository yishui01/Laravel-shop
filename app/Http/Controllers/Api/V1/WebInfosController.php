<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\WebInfo;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WebInfosController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Response::class
        return $this->response->array(WebInfo::all())->setStatusCode(201);
    }
}

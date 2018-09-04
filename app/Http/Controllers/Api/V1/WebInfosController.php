<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\WebInfo;

class WebInfosController extends Controller
{
    public function index()
    {
        return $this->response->array(WebInfo::first())
            ->setStatusCode($this->success_code);
    }
}

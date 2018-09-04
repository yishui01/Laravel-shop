<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Banner;
use App\Transformers\BannerTransformer;
use Illuminate\Http\Request;

class BannersController extends Controller
{
    public function index()
    {
        return $this->response->collection(Banner::show()->orderBy('sort', 'desc')
            ->get(), new BannerTransformer())->setStatusCode($this->success_code);
    }
}

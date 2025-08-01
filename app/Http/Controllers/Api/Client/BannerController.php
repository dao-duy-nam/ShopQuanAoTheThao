<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
public function index()
{
    return Banner::where('trang_thai', 'hien')
        ->orderBy('created_at', 'desc')
        ->get();
}

}

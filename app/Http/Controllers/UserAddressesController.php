<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
        return view('user_addresses.index', [
            'addresses'=>Auth::user()->addresses
        ]);
    }
}

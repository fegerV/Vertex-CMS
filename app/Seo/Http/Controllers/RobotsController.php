<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        return response(view('frontend.robots'))
            ->header('Content-Type', 'text/plain');
    }
}


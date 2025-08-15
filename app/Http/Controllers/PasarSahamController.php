<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PasarSahamController extends Controller
{
    public function index()
    {
        return Inertia::render('PasarSaham/Index');
    }

    public function chart()
    {
        return Inertia::render('PasarSaham/Chart');
    }

    public function watchlist()
    {
        return Inertia::render('PasarSaham/Watchlist');
    }

    public function trading()
    {
        return Inertia::render('Trading/Index');
    }
}
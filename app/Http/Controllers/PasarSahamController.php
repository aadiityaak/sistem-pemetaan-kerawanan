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

    public function screener()
    {
        return Inertia::render('PasarSaham/Screener');
    }

    public function heatmap()
    {
        return Inertia::render('PasarSaham/Heatmap');
    }

    public function chart()
    {
        return Inertia::render('PasarSaham/Chart');
    }

    public function watchlist()
    {
        return Inertia::render('PasarSaham/Watchlist');
    }
}
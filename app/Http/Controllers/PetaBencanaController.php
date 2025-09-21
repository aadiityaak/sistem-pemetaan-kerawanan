<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PetaBencanaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('PetaBencana/Index');
    }
}

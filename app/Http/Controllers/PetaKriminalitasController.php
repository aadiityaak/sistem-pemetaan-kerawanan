<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PetaKriminalitasController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('PetaKriminalitas/Index');
    }
}

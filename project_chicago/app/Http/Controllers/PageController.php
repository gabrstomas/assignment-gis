<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function homepage()
    {
        return redirect('/explore');
    }

    public function neighborhoods()
    {
        return view('pages.neighborhoods');
    }

    public function schools()
    {
        return view('pages.schools');
    }

    public function stations()
    {
        return view('pages.stations');
    }

    public function explore()
    {
        return view('pages.explore');
    }

    public function search()
    {
        return view('pages.search');
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Welcome to MVC Web8s',
            'message' => 'This is the new MVC structure!'
        ];

        $this->view('home', $data);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\SallaAuthService;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard');
    }
}


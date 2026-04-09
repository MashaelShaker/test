<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/oauth/callback',
        'api/webhook',//telling laravel its ok to let requests from this api in
        '/api/boxes',
    '/api/boxes/*',
    ];
}

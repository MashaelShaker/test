<?php

namespace App\Http\Controllers;

use App\Services\SallaAuthService;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class DashboardController extends Controller
{
    /**
     * @var SallaAuthService
     */
    private $salla;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SallaAuthService $salla)
    {
        //تعديل عشان اسوي تيست دون auth
//     $this->middleware('auth');
if (!env('DASHBOARD_BYPASS_AUTH', false)) {
$this->middleware('auth');
}
        $this->salla = $salla;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     * @throws IdentityProviderException
     */
    public function __invoke()

{
    $user = auth()->user();
if ($user && $user->token) {
    $this->salla->forUser($user);

    try {
        $this->salla->getNewAccessToken();
    } catch (IdentityProviderException $exception) {
        return redirect()->route('oauth.redirect');
    }
}

return view('dashboard');}}

  /**
  * if (auth()->user()->token) {
   *    $this->salla->forUser(auth()->user());

   *    try {
   *        $this->salla->getNewAccessToken();
   *    } catch (IdentityProviderException $exception) {
   *        return redirect()->route('oauth.redirect');
   *    }
   *}

   *return view('dashboard');
**/

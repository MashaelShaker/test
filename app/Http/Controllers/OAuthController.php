<?php
namespace App\Http\Controllers;

use App\Services\SallaAuthService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OAuthController extends Controller
{
    private $service;

    public function __construct(SallaAuthService $service)
    {
        $this->service = $service;
    }

    public function redirect()
    {
        return redirect($this->service->getProvider()->getAuthorizationUrl([
            'scope' => ['offline_access']
        ]));
    }

    public function callback(Request $request)
    {
        abort_if($this->service->isEasyMode(), 401, 'The Authorization mode is not supported');

        try {
            $token = $this->service->getAccessToken('authorization_code', [
                'code' => $request->code ?? ''
            ]);

            $user = $this->service->getResourceOwner($token);

            $userArray = $user->toArray();
            $storeId = $userArray['merchant']['id'] ?? null;

            $localUser = User::updateOrCreate(
                ['email' => $user->getEmail()],
                [
                    'name'     => $user->getName(),
                    'store_id' => $storeId,
                    'password' => bcrypt(\Illuminate\Support\Str::random(32)),
                ]
            );

            $localUser->token()->updateOrCreate([], [
                'access_token'  => $token->getToken(),
                'expires_in'    => $token->getExpires(),
                'refresh_token' => $token->getRefreshToken(),
                'merchant'      => $storeId,
            ]);

            auth()->login($localUser);

            return redirect('/boxes');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}

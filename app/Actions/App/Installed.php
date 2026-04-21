<?php

namespace App\Actions\App;

use App\Actions\BaseAction;
use Illuminate\Support\Facades\Artisan;

/**
 * @property string merchant example "1234509876"
 * @property string created_at example "Wed Jun 30 2021 14:32:33 GMT+0300"
 * @property string event example "app.installed"
 * @property array data @see https://docs.salla.dev/docs/merchent/ZG9jOjIzMjE3MjQ0-app-events#app-installation
 */
class Installed extends BaseAction
{
    public function handle(?string $merchant = null)
    {
        $arguments = [];
        if (!empty($merchant)) {
            $arguments['--merchant'] = $merchant;
        }

        Artisan::call('app:sync-products', $arguments);
        // you can do whatever you want
    }
}
